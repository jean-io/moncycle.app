<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "../config.php";
require_once "../lib/db.php";

header("Content-Type: text/plain");

print("MONCYCLE.APP");
print(PHP_EOL);

print("DB migration script from v14 to v15.");
print(PHP_EOL);

print("-----");
print(PHP_EOL);
print(PHP_EOL);

$db = db_open();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {

    $db->exec("START TRANSACTION");

	$statement_select_obs  = $db->prepare("SELECT no_observation, no_compte, date_obs, sensation FROM observation");
    $statement_select_desc = $db->prepare("SELECT * FROM description WHERE name LIKE :desc_name AND no_compte=:account_no LIMIT 1");
    $statement_insert_desc = $db->prepare("INSERT INTO `description` (`no_compte`, `name`, `type`) VALUES (:no_compte, :name, 0)");
    $statement_insert_link = $db->prepare("INSERT INTO `link_observation_description` (`no_observation`, `no_description`) VALUES (:observation_no, :description_no)");

	$statement_select_obs->execute();

	$observations = $statement_select_obs->fetchAll(PDO::FETCH_ASSOC);

    $cached_descriptions = [];

    // ITERATE ON ALL OBSERVATIONS
    foreach ($observations as $obs) {

        print("> compte " . $obs["no_compte"]);
        print("; obs " . $obs["no_observation"]);
        print(" " . $obs["date_obs"]);
        
        if (!isset($cached_descriptions[$obs["no_compte"]])) {
            $cached_descriptions[$obs["no_compte"]] = [];
        }

        if (isset($obs["sensation"]) && $obs["sensation"]!=null && !empty($obs["sensation"])) {

            // THERE IS SENSATION TO MIGRATE
            $sensations = explode(',', $obs["sensation"]);

            foreach ($sensations as $sens) {
                $sens = trim($sens);
                print(PHP_EOL);
                print("      # ");
                print($sens);
                
                $statement_select_desc->bindValue(":desc_name", $sens, PDO::PARAM_STR);
                $statement_select_desc->bindValue(":account_no", $obs["no_compte"], PDO::PARAM_INT);

	            $statement_select_desc->execute();
                $description = $statement_select_desc->fetch(PDO::FETCH_ASSOC);

                print(" ->");

                try {
                    $no_desc = 0;

                    if (isset($cached_descriptions[$obs["no_compte"]][$sens])) {
                        print(" Cached: "); // DESC NO PRESENT IN CACHE
                        $no_desc = $cached_descriptions[$obs["no_compte"]][$sens];
                    }
                    elseif (!isset($description["no_description"]) || isset($description["no_description"])<0) {
                        print(" Inserting: "); // INSERTING A NEW DESC FOR THIS ACCOUNT
                        $statement_insert_desc->bindValue(":no_compte", $obs["no_compte"], PDO::PARAM_INT);
                        $statement_insert_desc->bindValue(":name", $sens, PDO::PARAM_STR);
                        $statement_insert_desc->execute();
                        $no_desc = $db->lastInsertId();
                    }
                    else {
                        print(" Existing: "); // THIS DESC IS EXISTING FOR THIS ACCOUNT
                        $no_desc = intval($description["no_description"]);
                    }
    
                    print($no_desc); // ID OF DESCRIPTION
    
                    // CACHING DESCRIPTION NUMBER
                    if (!isset($cached_descriptions[$obs["no_compte"]][$sens])) {
                        $cached_descriptions[$obs["no_compte"]][$sens] = $no_desc;
                    }

                    $statement_insert_link->bindValue(":observation_no", $obs["no_observation"], PDO::PARAM_INT);
                    $statement_insert_link->bindValue(":description_no", $no_desc, PDO::PARAM_INT);
                    $statement_insert_link->execute();
                } catch (PDOException $th) {
                    print($th->getMessage());
                }

            }

        }
        else {
            print(" - NA");
        }

        print(PHP_EOL);
    }

    $db->exec("COMMIT");

} catch (\Throwable $th) {
    $db->exec("ROLLBACK");
    $db = null;

    throw $th;
}

$db = null;
