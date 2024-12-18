/*---------------------------------------------------------------CRIAÇÃO--DE--BANCO--DE--DADOS-----------------------------------*/
CREATE DATABASE PASSEINESSA;
USE PASSEINESSA;
SHOW TABLES;

/*---------------------------------------------------------------CRIAÇÃO--DE--TABELAS--------------------------------------------*/
CREATE TABLE TB_MATERIAS (
    ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    NM_MATERIA VARCHAR(5000) NOT NULL,
    ID_USUARIO INT NOT NULL,
    FOREIGN KEY (ID_USUARIO) REFERENCES TB_USUARIOS(ID)
);

CREATE TABLE TB_CONTEUDOS(
 ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
 NM_CONTEUDO VARCHAR (500) NOT NULL,
 ID_MATERIA INT,
 FOREIGN KEY (ID_MATERIA) REFERENCES TB_MATERIAS (ID)
);

CREATE TABLE TB_ATIVIDADE (
    ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    ID_MATERIA INT NOT NULL,
    DT_INICIO DATE NOT NULL,
	NR_HORA int,
    NR_MINUTO int,
	NR_SEGUNDO int,
    ID_USUARIO INT NOT NULL,
    ID_CONTEUDO INT,
    FOREIGN KEY (ID_USUARIO) REFERENCES TB_USUARIOS(ID),
    FOREIGN KEY (ID_MATERIA) REFERENCES TB_MATERIAS(ID),
    FOREIGN KEY (ID_CONTEUDO) REFERENCES TB_CONTEUDOS(ID)
);


CREATE TABLE TB_USUARIOS (
    ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    NM_EMAIL VARCHAR(255) NOT NULL UNIQUE,
    VL_SENHA VARCHAR(255) NOT NULL,
    NM_NOME VARCHAR(100) NOT NULL,
    IS_ATIVO BOOLEAN DEFAULT TRUE,
    DT_CRIACAO TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/*----------------------------------------------------------CONSULTAS----------------------------------------------------------

select* from tb_materias;
select* from tb_conteudos;
select * from TB_USUARIOS;
select * from tb_atividade;

SELECT 
    U.NM_NOME AS Nome_Usuario,
    M.NM_MATERIA AS Nome_Materia,
    C.NM_CONTEUDO AS Nome_Conteudo,
    CONCAT(A.NR_HORA, 'h ', A.NR_MINUTO, 'm ', A.NR_SEGUNDO, 's') AS Atividade
FROM 
    TB_ATIVIDADE A
JOIN 
    TB_USUARIOS U ON A.ID_USUARIO = U.ID
JOIN 
    TB_MATERIAS M ON A.ID_MATERIA = M.ID
JOIN 
    TB_CONTEUDOS C ON A.ID_CONTEUDO = C.ID;

*/

                
                