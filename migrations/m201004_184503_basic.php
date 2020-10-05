<?php

use yii\db\Migration;

/**
 * Class m201004_184503_basic
 */
class m201004_184503_basic extends Migration
{

    // migration code without a transaction.
    public function up()
    {
        $this->execute("drop view IF EXISTS `lpd_page_v`;
                            drop table IF EXISTS `lpd_log`;
                            drop table IF EXISTS `lpd_file`;
                            drop table IF EXISTS `lpd_page`;
                            drop table IF EXISTS `pg_category`;
                            drop table IF EXISTS `lpd_user`;
                            
");

        $this->execute("
drop table IF EXISTS `lpd_user`;
CREATE TABLE `lpd_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `authKey` varchar(100),
  `accessToken` varchar(30),
  `is_locked` char(1)  NOT NULL default 'F',
  PRIMARY KEY (`id`),
  unique uk_lpd_user (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
create index i_lpd_user_at on lpd_user (accessToken);

drop table IF EXISTS `pg_category`;
create Table `pg_category` (
 `id` int not null AUTO_INCREMENT,
 `code` varchar(30) not null,
 `descr` varchar(255),
 `begin_date` date,
 `end_date` date,
  PRIMARY KEY (`id`)
 ,Unique key (`code`) 
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;

drop table IF EXISTS `lpd_page`;
create Table `lpd_page` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `date_create` datetime NOT NULL default CURRENT_TIMESTAMP,
  `category_id` int not null,
  `url_from` varchar(255),
  `user_id` int,
  PRIMARY KEY (`id`),
  unique uk_lpd_page (`name`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
  ");


        $this->execute("drop table IF EXISTS `lpd_file`;
create Table `lpd_file` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_id` int NOT NULL, 
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255),
  PRIMARY KEY (`id`),
  CONSTRAINT fk_lpd_file_p 
    FOREIGN KEY (page_id)  REFERENCES lpd_page (id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create index i_lpd_file on lpd_file (page_id);

drop table IF EXISTS `lpd_log`;
create Table `lpd_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int, 
  `date_create` datetime NOT NULL default CURRENT_TIMESTAMP,
  `action` varchar(20),
  `ip` varchar(16),
  `user_agent` varchar(255),
  `extra_info` varchar(200),
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create index i_lpd_log_u on lpd_log (username);

");


                $this->execute("create or replace view lpd_page_v as
        select p.id
              ,p.name
              ,p.date_create
              ,p.url_from
              ,c.code category
              ,c.descr category_descr
              ,u.username
        from lpd_page p
        join pg_category c
        on (p.category_id = c.id)
        left join lpd_user u
        on (p.user_id = u.id);
        ");

        $this->batchInsert('pg_category',['code','descr','begin_date','end_date']
                            ,[['Smartlink','Smartlink ...','2020-01-01', '3000-01-01'],
                              ['LeadGen','LeadGen ...','2020-01-01', '3000-01-01'],
                              ['Test','for any tests','2020-01-01', '3000-01-01'],
                              ['Test_OLD','closes test category','2020-01-01', '2020-02-01']
                             ]);


    }


    public function down()
    {

/* --
        $this->dropTable('lpd_log');
        $this->dropTable('lpd_file');
        $this->dropTable('lpd_page');
        $this->dropTable('pg_category');
        $this->dropTable('lpd_user');
*/
        $this->execute("drop view IF EXISTS `lpd_page_v`;
                            drop table IF EXISTS `lpd_log`;
                            drop table IF EXISTS `lpd_file`;
                            drop table IF EXISTS `lpd_page`;
                            drop table IF EXISTS `pg_category`;
                            drop table IF EXISTS `lpd_user`;
                            
");
    }
}
