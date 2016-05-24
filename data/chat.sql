CREATE TABLE `chat` (
`id`  int NOT NULL AUTO_INCREMENT ,
`from`  varchar(30) NOT NULL DEFAULT '' ,
`to`  varchar(30) NOT NULL DEFAULT '' ,
`content`  varchar(500) NOT NULL DEFAULT '' ,
`addtime`  int NOT NULL DEFAULT 0 ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
;



CREATE TABLE `chat` (
`id`  int NOT NULL AUTO_INCREMENT ,
`from`  varchar(30) NOT NULL DEFAULT '' ,
`to`  varchar(30) NOT NULL DEFAULT '' ,
`type`  tinyint NOT NULL DEFAULT 1 ,
`content`  varchar(500) NOT NULL DEFAULT '' ,
`addtime`  int NOT NULL DEFAULT 0 ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci
;


insert into chat values('','崔凯','谢苏文', 1, '呵呵',0);