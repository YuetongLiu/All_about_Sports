--drops
drop table usersSubscribePlayer;
drop table usersSubscribeTeam;
drop table perform;
drop table playerSignContract;
drop table coachSignContract;
drop table matches;
drop table vs;
drop table teamIn;
drop table contracts;
drop table leagues;
drop table arenas;
drop table users;
drop view default_display;
commit;

--creates

create table arenas (
	arena_name CHAR (50) NOT NULL ,
	location CHAR (100) NOT NULL ,
	capacity INTEGER NOT NULL ,
	PRIMARY KEY (arena_name)
	);

grant select on arenas to public;

create table leagues (
	league_name CHAR (50) NOT NULL ,
    gameType CHAR (50) NOT NULL,
	PRIMARY KEY ( league_name)
	);

grant select on leagues to public;

create table matches (
	mdate TIMESTAMP NOT NULL ,
	teamA_name CHAR (50) NOT NULL ,
	teamA_score INTEGER ,
	teamB_name CHAR (50) NOT NULL ,
	teamB_score INTEGER ,
	arena_name CHAR (50) ,
 	ticket_price INTEGER ,
	num_of_audience INTEGER ,
	league_name CHAR (50) NOT NULL,
	PRIMARY KEY (mdate , teamA_name , teamB_name),
	FOREIGN KEY (arena_name) REFERENCES arenas (arena_name)
 	ON DELETE CASCADE,
	FOREIGN KEY ( league_name) REFERENCES leagues (league_name)
	ON DELETE CASCADE
	);

grant select on matches to public;


create table users (
	users_id INTEGER NOT NULL ,
	isAdmin NUMBER(1,0) NOT NULL,
	PRIMARY KEY ( users_id)
	);

grant select on users to public;

create table teamIn (
	team_name CHAR (50) NOT NULL ,
	league_name CHAR (50) NOT NULL ,
	PRIMARY KEY (team_name , league_name),
	FOREIGN KEY ( league_name) REFERENCES leagues ( league_name)
	ON DELETE CASCADE
	);

grant select on teamIn to public;

create table vs(
	team_name1 CHAR (50) NOT NULL ,
	team_name2 CHAR (50) NOT NULL ,
	league_name CHAR (50) NOT NULL ,
	PRIMARY KEY (team_name1 , team_name2),
	FOREIGN KEY (team_name1, league_name) REFERENCES teamIn (team_name, league_name)
	ON DELETE CASCADE,
	FOREIGN KEY (team_name2, league_name) REFERENCES teamIn (team_name, league_name)
	ON DELETE CASCADE
	);

grant select on vs to public;

create table contracts (
	contract_no INTEGER NOT NULL , 
  	cdate DATE ,
	price INTEGER ,
	PRIMARY KEY (contract_no)
	);

grant select on contracts to public;

create table playerSignContract (
	player_id INTEGER NOT NULL ,
	dob DATE ,
	position CHAR (50) ,
	name CHAR (50) ,
	contract_no INTEGER NOT NULL ,
    team_name CHAR (50) ,
    league_name CHAR (50) NOT NULL ,
	PRIMARY KEY ( player_id),
	FOREIGN KEY ( contract_no) REFERENCES contracts ( contract_no)
	ON DELETE SET NULL,
    FOREIGN KEY (team_name, league_name) REFERENCES teamIn (team_name,league_name)
	ON DELETE SET NULL,
	UNIQUE(contract_no)
	);

grant select on playerSignContract to public;

create table coachSignContract (
	coach_id INTEGER NOT NULL ,
	dob DATE ,
	name CHAR (50) NOT NULL ,
	contract_no INTEGER NOT NULL ,
	team_name CHAR (50) ,
	league_name CHAR (50) NOT NULL ,
	PRIMARY KEY ( coach_id) ,
	FOREIGN KEY ( contract_no) REFERENCES contracts
	ON DELETE SET NULL,
    FOREIGN KEY (team_name, league_name) REFERENCES teamIn (team_name,league_name)
	ON DELETE SET NULL,
	UNIQUE(contract_no)
	);

grant select on coachSignContract to public;

create table perform (
	mdate TIMESTAMP NOT NULL ,
	teamA_name CHAR (50) NOT NULL ,
	teamB_name CHAR (50) NOT NULL ,
	player_id INTEGER NOT NULL ,
	score INTEGER ,
	assist INTEGER ,
	PRIMARY KEY (mdate , teamA_name , teamB_name , player_id),
	FOREIGN KEY (mdate , teamA_name , teamB_name) REFERENCES matches (mdate , teamA_name , teamB_name)
	ON DELETE CASCADE,
	FOREIGN KEY (player_id) REFERENCES playerSignContract (player_id)
	ON DELETE CASCADE
	);

grant select on perform to public;

create table usersSubscribePlayer (
	users_id INTEGER NOT NULL ,
	player_id INTEGER NOT NULL ,
	PRIMARY KEY (users_id , player_id),
 	FOREIGN KEY (users_id) REFERENCES users (users_id)
	ON DELETE CASCADE,
	FOREIGN KEY (player_id) REFERENCES playerSignContract (player_id)
	ON DELETE SET NULL
	);

grant select on usersSubscribePlayer to public;

create table usersSubscribeTeam (
	users_id INTEGER NOT NULL ,
	team_name CHAR (50) NOT NULL ,
	league_name CHAR (50) NOT NULL ,
	PRIMARY KEY (users_id , team_name),
	FOREIGN KEY (users_id) REFERENCES users (users_id)
	ON DELETE CASCADE,
	FOREIGN KEY (team_name, league_name) REFERENCES teamIn(team_name, league_name)
	ON DELETE SET NULL
	);
    
grant select on usersSubscribeTeam to public;

commit;
  
--insert arenas


insert into arenas
values('Golden 1 Center',
		'500 David J Stern Walk, Sacramento, CA 95814, USA',
        17608);
insert into arenas
values('Staples Center',
		'1111 S Figueroa St, Los Angeles, CA 90015, USA',
        21000);
insert into arenas 
values('Stamford Bridge Stadium',
	   'Fulham Rd, Fulham, London, SW6 1HS, UK',
	    41631);
insert into arenas 
values('Emirates Stadium',
	   'Hornsey Rd, London, N7 7AJ, UK',
	    59867);
        

commit;
--insert users


insert into users
values(100,0);
insert into users
values(101,1);

commit;
--insert leagues


insert into leagues
values('NBA', 'basketball');
insert into leagues
values('Premier League','soccer');



--insert teamIn


insert into teamIn
values('San Antonio Spurs', 'NBA');
insert into teamIn
values('Sacramento Kings', 'NBA');
insert into teamIn
values('Toronto Raptors', 'NBA');
insert into teamIn
values('Los Angeles Lakers', 'NBA');

insert into teamIn
values('Arsenal','Premier League');
insert into teamIn
values('Liverpool','Premier League');
insert into teamIn
values('Chelsea','Premier League');
insert into teamIn
values('Manchester United ','Premier League');


commit;

-- insert vs

insert into vs
values('Toronto Raptors', 'Los Angeles Lakers','NBA');
insert into vs
values('San Antonio Spurs', 'Sacramento Kings','NBA');
insert into vs
values('San Antonio Spurs', 'Toronto Raptors','NBA');
insert into vs
values('San Antonio Spurs', 'Los Angeles Lakers','NBA');
insert into vs
values('Toronto Raptors', 'Sacramento Kings','NBA');
insert into vs
values('Los Angeles Lakers', 'Sacramento Kings','NBA');


insert into vs
values('Arsenal','Liverpool','Premier League');
insert into vs
values('Chelsea','Arsenal','Premier League');
insert into vs
values('Arsenal','Manchester United','Premier League');
insert into vs
values('Chelsea','Liverpool','Premier League');
insert into vs
values('Manchester United','Liverpool','Premier League');
insert into vs
values('Chelsea','Manchester United','Premier League');


commit;
-- insert matches

insert into matches
values('2018-11-04 18:30:00',
		'Toronto Raptors',121,
        'Los Angeles Lakers',107,
        'Staples Center',
        NULL,NULL,'NBA');
        
insert into matches
values('2018-11-02 19:00:00',
		'San Antonio Spurs', 99,
        'Sacramento Kings', 104,
        'Golden 1 Center',
        NULL,NULL,'NBA');

insert into matches
values('2018-11-05 18:30:00',
		'San Antonio Spurs',121,
        'Toronto Raptors',107,
        'Staples Center',
        NULL,NULL,'NBA');
        
insert into matches
values('2018-11-06 19:00:00',
		'San Antonio Spurs', 99,
        'Los Angeles Lakers', 104,
        'Golden 1 Center',
        NULL,NULL,'NBA');

insert into matches
values('2018-11-07 18:30:00',
		'Toronto Raptors',121,
        'Sacramento Kings',107,
        'Staples Center',
        NULL,NULL,'NBA');
        
insert into matches
values('2018-11-08 19:00:00',
		'Los Angeles Lakers', 99,
        'Sacramento Kings', 104,
        'Golden 1 Center',
        NULL,NULL,'NBA');

insert into matches
values('2018-08-18 09:30:00',
       'Chelsea',3,'Arsenal',2,
       'Stamford Bridge Stadium',
       NULL, NULL,'Premier League');
    
insert into matches
values('2018-11-03 10:30:00',
       'Arsenal',1,'Liverpool',1,
       'Emirates Stadium',
       NULL, NULL,'Premier League');

insert into matches
values('2018-08-19 09:30:00',
       'Arsenal',3,'Manchester United',2,
       'Stamford Bridge Stadium',
       NULL, NULL,'Premier League');
    
insert into matches
values('2018-11-04 10:30:00',
       'Chelsea',1,'Liverpool',1,
       'Emirates Stadium',
       NULL, NULL,'Premier League');

insert into matches
values('2018-08-20 09:30:00',
       'Manchester United',3,'Liverpool',2,
       'Stamford Bridge Stadium',
       NULL, NULL,'Premier League');
    
insert into matches
values('2018-11-05 10:30:00',
       'Chelsea',1,'Manchester United',1,
       'Emirates Stadium',
       NULL, NULL,'Premier League');


commit;
       
--insert contracts

insert into contracts 
values(1001, '2015-05-05',310000);
insert into contracts 
values(1002, '2011-09-01',320000);
insert into contracts 
values(1003, '2012-03-21',330000);
insert into contracts 
values(1004, '2015-09-21',340000);
insert into contracts 
values(1005, '2012-05-11',350000);
insert into contracts 
values(1006, '2013-07-11',360000);
insert into contracts
values(1007, '1996-09-05',370000);
insert into contracts 
values(1008, '2015-01-04',380000);
insert into contracts 
values(1009, '2015-05-05',310000);
insert into contracts 
values(1010, '2011-09-01',320000);
insert into contracts 
values(1011, '2012-03-21',330000);
insert into contracts 
values(1012, '2015-09-21',340000);
insert into contracts 
values(1013, '2012-05-11',350000);
insert into contracts 
values(1014, '2013-07-11',360000);
insert into contracts
values(1015, '1996-09-05',370000);
insert into contracts 
values(1016, '2015-01-04',380000);


insert into contracts 
values(2001, '2013-09-01',350000);
insert into contracts 
values(2002, '2016-07-01',290000);
insert into contracts 
values(2003, '2017-07-01',200000);
insert into contracts 
values(2004, '2018-05-23',115000);
insert into contracts 
values(2005, '2018-07-14',NULL);
insert into contracts 
values(2006, '2015-11-09',135000);
insert into contracts 
values(2007, '2013-09-01',350000);
insert into contracts 
values(2008, '2016-07-01',290000);
insert into contracts 
values(2009, '2017-07-01',200000);
insert into contracts 
values(2010, '2018-05-23',115000);
insert into contracts 
values(2011, '2018-07-14',NULL);
insert into contracts 
values(2012, '2015-11-09',135000);
insert into contracts 
values(2013, '2013-09-01',350000);
insert into contracts 
values(2014, '2016-07-01',290000);
insert into contracts 
values(2015, '2017-07-01',200000);
insert into contracts 
values(2016, '2018-05-23',115000);
insert into contracts 
values(2017, '2018-07-14',NULL);
insert into contracts 
values(2018, '2015-11-09',135000);
insert into contracts 
values(2019, '2016-07-01',290000);
insert into contracts 
values(2020, '2017-07-01',200000);
insert into contracts 
values(2021, '2018-05-23',115000);
insert into contracts 
values(2022, '2018-07-14',NULL);
insert into contracts 
values(2023, '2015-11-09',135000);
insert into contracts 
values(2024, '2015-11-09',135000);
       

commit;
--insert playerSignContract

insert into playerSignContract 
values(1001, '1994-04-02','Power Forward',
	'Pascal Siakam',1001, 'Toronto Raptors','NBA');

insert into playerSignContract 
values(1009, '1991-06-29','Power Forward',
	'Kawhi Leonard',1009, 'Toronto Raptors','NBA');

insert into playerSignContract 
values(1010, '1993-05-25','Swingman',
	'Norman owell',1010, 'Toronto Raptors','NBA');

insert into playerSignContract 
values(1002, '1984-12-30','Small Forward',
	'LeBron James',1002, 'Los Angeles Lakers','NBA');

insert into playerSignContract 
values(1011, '1997-10-27','Point Ground',
	'Lonzo Ball',1011, 'Los Angeles Lakers','NBA');

insert into playerSignContract 
values(1012, '1982-10-02','Center',
	'Tyson Chandler',1012, 'Los Angeles Lakers','NBA');

insert into playerSignContract 
values(1003, '1989-08-07','Shooting Guard',
	'DeMar DeRozan',1003, 'San Antonio Spurs','NBA');

insert into playerSignContract 
values(1013, '1980-07-06','Center',
	'Pau Gasol',1013, 'San Antonio Spurs','NBA');

insert into playerSignContract 
values(1014, '1988-08-11','Shooting Guard',
	'Patty Mills',1014, 'San Antonio Spurs','NBA');

insert into playerSignContract 
values(1004, '1988-05-09','Power Forward',
	'Nemanja Bjelica',1004, 'Sacramento Kings','NBA');

insert into playerSignContract 
values(1015, '1993-12-17','Shooting Guard',
	'Buddy Hield',1015, 'Sacramento Kings','NBA');

insert into playerSignContract 
values(1016, '1999-03-14','Power Forward',
	'Marvin Bagley',1016, 'Sacramento Kings','NBA');

insert into playerSignContract 
values(2001, '1988-10-15','Midfielder','Mesut Özil',2001, 'Arsenal','Premier League');

insert into playerSignContract 
values(2007, '1982-05-20','Goalkeeper','Petr Čech',2007, 'Arsenal','Premier League');

insert into playerSignContract 
values(2008, '1990-12-26','Midfielder','Aaron Ramsey',2008, 'Arsenal','Premier League');

insert into playerSignContract 
values(2009, '1991-05-28','Forward','Alexandre Lacazette',2009, 'Arsenal','Premier League');

insert into playerSignContract 
values(2010, '1995-03-19','Defender','Héctor Bellerín',2010, 'Arsenal','Premier League');

insert into playerSignContract 
values(2002, '1991-03-23','Midfielder','N Golo Kanté',2002, 'Chelsea','Premier League');

insert into playerSignContract 
values(2011, '1994-10-03','Goalkeeper','Kepa Arrizabalaga',2011, 'Chelsea','Premier League');

insert into playerSignContract 
values(2012, '1990-12-28','Defender','Marcos Alonso',2012, 'Chelsea','Premier League');

insert into playerSignContract 
values(2013, '1986-09-30','Forward','Olivier Giroud',2013, 'Chelsea','Premier League');

insert into playerSignContract 
values(2014, '1993-03-03','Defender','Antonio Rüdiger',2014, 'Chelsea','Premier League');

insert into playerSignContract 
values(2003, '1992-06-25','Forward','Mohamed Salah Ghaly', 2003, 'Liverpool','Premier League');

insert into playerSignContract 
values(2015, '1991-05-05','Defender','Nathaniel Clyne', 2015, 'Liverpool','Premier League');

insert into playerSignContract 
values(2016, '1993-10-23','Midfielder','Fábio Henrique Tavares', 2016, 'Liverpool','Premier League');

insert into playerSignContract 
values(2017, '1992-05-10','Forward','Sadio Mané', 2017, 'Liverpool','Premier League');

insert into playerSignContract 
values(2018, '1991-10-02','Forward','Roberto Firmino', 2018, 'Liverpool','Premier League');

insert into playerSignContract 
values(2019, '1992-10-02','Goalkeeper','David de Gea', 2019, 'Manchester United','Premier League');

insert into playerSignContract 
values(2020, '1993-10-02','Defender','Victor Lindelöf', 2020, 'Manchester United','Premier League');

insert into playerSignContract 
values(2021, '1994-10-02','Midfielder','Paul Pogba', 2021, 'Manchester United','Premier League');

insert into playerSignContract 
values(2022, '1995-10-02','Forward','Alexis Sánchez', 2022, 'Manchester United','Premier League');

insert into playerSignContract 
values(2023, '1996-10-02','Forward','Anthony Martial', 2023, 'Manchester United','Premier League');


commit;
--insert coachSignContract


insert into coachSignContract 
values(1005, '1967-07-24','Nick Nurse',
	1005, 'Toronto Raptors','NBA');

insert into coachSignContract 
values(1006, '1980-03-28','Luke Walton',
	1006, 'Los Angeles Lakers','NBA');

insert into coachSignContract 
values(1007, '1949-01-28','Gregg Popovich',
	1007, 'San Antonio Spurs','NBA');

insert into coachSignContract 
values(1008, '1974-02-21','Dave Joerger',
	1008, 'Sacramento Kings','NBA');

insert into coachSignContract 
values(2004, '1971-11-03','Unai Emery Etxegoien',2004, 'Arsenal','Premier League');

insert into coachSignContract 
values(2005, '1959-01-10','Maurizio Sarri',2005, 'Chelsea','Premier League');

insert into coachSignContract 
values(2006, '1967-06-16','Jürgen Norbert Klopp',2006, 'Liverpool','Premier League');

insert into coachSignContract 
values(2024, '1977-06-16','José Mourinho',2024, 'Manchester United','Premier League');

commit;
--insrt perform


insert into perform
values('2018-11-02 19:00:00', 'San Antonio Spurs', 'Sacramento Kings', 1003, 23, 8);

insert into perform
values('2018-11-02 19:00:00', 'San Antonio Spurs', 'Sacramento Kings', 1004, 11, 0);

insert into perform
values('2018-11-02 19:00:00', 'San Antonio Spurs', 'Sacramento Kings', 1013, 23, 8);

insert into perform
values('2018-11-02 19:00:00', 'San Antonio Spurs', 'Sacramento Kings', 1014, 11, 0);

insert into perform
values('2018-11-02 19:00:00', 'San Antonio Spurs', 'Sacramento Kings', 1015, 23, 8);

insert into perform
values('2018-11-02 19:00:00', 'San Antonio Spurs', 'Sacramento Kings', 1016, 11, 0);


insert into perform
values('2018-11-04 18:30:00', 'Toronto Raptors', 'Los Angeles Lakers', 1001, 16, 3);

insert into perform
values('2018-11-04 18:30:00', 'Toronto Raptors', 'Los Angeles Lakers', 1002, 18, 6);

insert into perform
values('2018-11-04 18:30:00', 'Toronto Raptors', 'Los Angeles Lakers', 1009, 16, 3);

insert into perform
values('2018-11-04 18:30:00', 'Toronto Raptors', 'Los Angeles Lakers', 1010, 18, 6);


insert into perform
values('2018-11-05 18:30:00', 'San Antonio Spurs', 'Toronto Raptors', 1001, 18, 6);

insert into perform
values('2018-11-05 18:30:00', 'San Antonio Spurs', 'Toronto Raptors', 1009, 18, 6);

insert into perform
values('2018-11-05 18:30:00', 'San Antonio Spurs', 'Toronto Raptors', 1003, 18, 6);

insert into perform
values('2018-11-05 18:30:00', 'San Antonio Spurs', 'Toronto Raptors', 1013, 18, 6);


insert into perform
values('2018-11-06 19:00:00', 'San Antonio Spurs', 'Los Angeles Lakers', 1003, 18, 6);

insert into perform
values('2018-11-06 19:00:00', 'San Antonio Spurs', 'Los Angeles Lakers', 1013, 18, 6);

insert into perform
values('2018-11-06 19:00:00', 'San Antonio Spurs', 'Los Angeles Lakers', 1002, 18, 6);

insert into perform
values('2018-11-06 19:00:00', 'San Antonio Spurs', 'Los Angeles Lakers', 1012, 18, 6);

        
insert into perform
values('2018-11-07 18:30:00', 'Toronto Raptors', 'Sacramento Kings', 1001, 18, 6);

insert into perform
values('2018-11-07 18:30:00', 'Toronto Raptors', 'Sacramento Kings', 1009, 18, 6);

insert into perform
values('2018-11-07 18:30:00', 'Toronto Raptors', 'Sacramento Kings', 1004, 18, 6);

insert into perform
values('2018-11-07 18:30:00', 'Toronto Raptors', 'Sacramento Kings', 1015, 18, 6);


insert into perform
values('2018-11-08 19:00:00', 'Los Angeles Lakers', 'Sacramento Kings', 1002, 18, 6);

insert into perform
values('2018-11-08 19:00:00', 'Los Angeles Lakers', 'Sacramento Kings', 1011, 18, 6);

insert into perform
values('2018-11-08 19:00:00', 'Los Angeles Lakers', 'Sacramento Kings', 1004, 18, 6);

insert into perform
values('2018-11-08 19:00:00', 'Los Angeles Lakers', 'Sacramento Kings', 1015, 18, 6);
        


insert into perform
values('2018-08-18 09:30:00', 'Chelsea', 'Arsenal', 2001, 1, 0);

insert into perform
values('2018-08-18 09:30:00', 'Chelsea', 'Arsenal', 2007, 0, 0);

insert into perform
values('2018-08-18 09:30:00', 'Chelsea', 'Arsenal', 2008, 1, 0);

insert into perform
values('2018-08-18 09:30:00', 'Chelsea', 'Arsenal', 2002, 0, 0);

insert into perform
values('2018-08-18 09:30:00', 'Chelsea', 'Arsenal', 2011, 1, 0);

insert into perform
values('2018-08-18 09:30:00', 'Chelsea', 'Arsenal', 2014, 0, 0);


insert into perform
values('2018-11-03 10:30:00', 'Arsenal', 'Liverpool', 2001, 1, 0);

insert into perform
values('2018-11-03 10:30:00', 'Arsenal', 'Liverpool', 2007, 1, 0);

insert into perform
values('2018-11-03 10:30:00', 'Arsenal', 'Liverpool', 2008, 0, 0);

insert into perform
values('2018-11-03 10:30:00', 'Arsenal', 'Liverpool', 2003, 1, 0);

insert into perform
values('2018-11-03 10:30:00', 'Arsenal', 'Liverpool', 2015, 0, 0);

insert into perform
values('2018-11-03 10:30:00', 'Arsenal', 'Liverpool', 2016, 1, 1);


insert into perform
values('2018-08-19 09:30:00', 'Arsenal', 'Manchester United', 2001, 1, 0);

insert into perform
values('2018-08-19 09:30:00', 'Arsenal', 'Manchester United', 2009, 0, 0);

insert into perform
values('2018-08-19 09:30:00', 'Arsenal', 'Manchester United', 2007, 0, 0);

insert into perform
values('2018-08-19 09:30:00', 'Arsenal', 'Manchester United', 2019, 1, 0);

insert into perform
values('2018-08-19 09:30:00', 'Arsenal', 'Manchester United', 2020, 0, 0);

insert into perform
values('2018-08-19 09:30:00', 'Arsenal', 'Manchester United', 2021, 1, 0);


insert into perform
values('2018-11-04 10:30:00', 'Chelsea', 'Liverpool', 2002, 1, 0);

insert into perform
values('2018-11-04 10:30:00', 'Chelsea', 'Liverpool', 2011, 1, 0);

insert into perform
values('2018-11-04 10:30:00', 'Chelsea', 'Liverpool', 2012, 0, 0);

insert into perform
values('2018-11-04 10:30:00', 'Chelsea', 'Liverpool', 2003, 1, 1);

insert into perform
values('2018-11-04 10:30:00', 'Chelsea', 'Liverpool', 2016, 0, 0);

insert into perform
values('2018-11-04 10:30:00', 'Chelsea', 'Liverpool', 2015, 1, 0);

    
insert into perform
values('2018-08-20 09:30:00', 'Manchester United', 'Liverpool', 2019, 1, 0);

insert into perform
values('2018-08-20 09:30:00', 'Manchester United', 'Liverpool', 2021, 0, 0);

insert into perform
values('2018-08-20 09:30:00', 'Manchester United', 'Liverpool', 2022, 1, 0);

insert into perform
values('2018-08-20 09:30:00', 'Manchester United', 'Liverpool', 2015, 0, 1);

insert into perform
values('2018-08-20 09:30:00', 'Manchester United', 'Liverpool', 2003, 1, 0);

insert into perform
values('2018-08-20 09:30:00', 'Manchester United', 'Liverpool', 2017, 1, 0);



insert into perform
values('2018-11-05 10:30:00', 'Chelsea', 'Manchester United', 2002, 0, 0);

insert into perform
values('2018-11-05 10:30:00', 'Chelsea', 'Manchester United', 2011, 0, 0);

insert into perform
values('2018-11-05 10:30:00', 'Chelsea', 'Manchester United', 2013, 1, 0);

insert into perform
values('2018-11-05 10:30:00', 'Chelsea', 'Manchester United', 2019, 0, 1);

insert into perform
values('2018-11-05 10:30:00', 'Chelsea', 'Manchester United', 2020, 1, 0);

insert into perform
values('2018-11-05 10:30:00', 'Chelsea', 'Manchester United', 2021, 1, 0);

CREATE VIEW default_display AS
SELECT mdate, teamA_name, teamA_score, teamB_score, teamB_name
FROM matches
ORDER BY mdate desc;

commit;
