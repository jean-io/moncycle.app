delete a from observation a join (select no_observation from observation group by date_obs, no_compte having count(no_observation)>1) b on a.no_observation = b.no_observation;
delete a from observation a join (select no_observation from observation group by date_obs, no_compte having count(no_observation)>1) b on a.no_observation = b.no_observation;
delete a from observation a join (select no_observation from observation group by date_obs, no_compte having count(no_observation)>1) b on a.no_observation = b.no_observation;
delete a from observation a join (select no_observation from observation group by date_obs, no_compte having count(no_observation)>1) b on a.no_observation = b.no_observation;
delete a from observation a join (select no_observation from observation group by date_obs, no_compte having count(no_observation)>1) b on a.no_observation = b.no_observation;
delete a from observation a join (select no_observation from observation group by date_obs, no_compte having count(no_observation)>1) b on a.no_observation = b.no_observation;
delete a from observation a join (select no_observation from observation group by date_obs, no_compte having count(no_observation)>1) b on a.no_observation = b.no_observation;
delete a from observation a join (select no_observation from observation group by date_obs, no_compte having count(no_observation)>1) b on a.no_observation = b.no_observation;
delete a from observation a join (select no_observation from observation group by date_obs, no_compte having count(no_observation)>1) b on a.no_observation = b.no_observation;
delete a from observation a join (select no_observation from observation group by date_obs, no_compte having count(no_observation)>1) b on a.no_observation = b.no_observation;
delete a from observation a join (select no_observation from observation group by date_obs, no_compte having count(no_observation)>1) b on a.no_observation = b.no_observation;

alter table observation add constraint unique_compte_and_date unique (no_compte, date_obs);
