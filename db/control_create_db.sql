

#tipos de usuários - Vincular ao indice de acessos
CREATE TABLE `user_type`(
  `typecod`   INTEGER UNSIGNED NOT NULL COMMENT "Codigo do tipo de usuario",
  `typename`  VARCHAR(40) COMMENT "Nome do tipo de usuário",
  `typesmall` CHAR(3) NOT NULL COMMENT "Abreviação do tipo de usuario, usado pelo login",
  CONSTRAINT pk_user_type PRIMARY KEY (`typecod`),
  CONSTRAINT indTypeSmall UNIQUE KEY (`typesmall`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8;



#Usuários do sistema
CREATE TABLE `user`(
  `usrcod`      INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Codigo do usuario",
  `typecod`     INTEGER UNSIGNED NOT NULL COMMENT "Codigo do tipo de usuario",
  `usrname`     VARCHAR(40) NOT NULL COMMENT "Nome do usuario",
  `usrlogin`    VARCHAR(40) NOT NULL COMMENT "Login do usuario",
  `usremail`    VARCHAR(70) NOT NULL COMMENT "Email do usuario, geralmente pode ser usado no lugar do login",
  `usrpwd`      CHAR(128) NOT NULL COMMENT "Senha do usuario, uma hash de 128bytes",
  `usractive`   TINYINT NOT NULL DEFAULT 0 COMMENT "Se o usuario esta ou nao ativo",
  `usrdatecad`  DATETIME NOT NULL COMMENT "Data de cadastro",
  `usrdatealt`  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP COMMENT "Data da ultima alteracao",
  CONSTRAINT pk_user     PRIMARY KEY (`usrcod`),
  CONSTRAINT fk_usrtype  FOREIGN KEY (`typecod`) REFERENCES `user_type`(`typecod`) ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT indUsrLogin UNIQUE  KEY (`usrlogin`),
  CONSTRAINT indUsrEmail UNIQUE  KEY (`usremail`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8;



#TABELA DE LOG DE HORARIOS
CREATE TABLE `timelog`(
  `tmlcod`     INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `usrcod`     INTEGER UNSIGNED NOT NULL,
  `tmldata`    DATETIME NOT NULL,
  `tmltime`    INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `tmldesc`    VARCHAR(120),
  `tmldatecad` DATETIME NOT NULL,
  `tmldatealt` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  `tmlactive`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT `pk_timelog` PRIMARY KEY (`tmlcod`),
  CONSTRAINT `fk_tmlusr`  FOREIGN KEY (`usrcod`) REFERENCES `user`(`usrcod`) ON UPDATE CASCADE ON DELETE NO ACTION
) ENGINE=InnoDb DEFAULT CHARSET=utf8;