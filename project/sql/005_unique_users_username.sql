ALTER TABLE Users
ADD UNIQUE (username);
MODIFY username varchar(60) NOT NULL;
