CREATE TABLE IF NOT EXISTS `Competitions` (
	id int auto_increment,
    name varchar(30) not null,
    created timestamp default CURRENT_TIMESTAMP,
    duration int default 3,
    expires timestamp,
    cost int default 1,
    reward int default 0,
    participants int default 0,
    paid_out tinyint default 0,
    min_score int default 1,
    first_place_per float default 1,
    second_place_per float default 0.0,
    third_place_per float default 0.0,
    fee int default 0,
    creator_id int,
    primary key (id),
    foreign key (creator_id) references Users(id)
	)
