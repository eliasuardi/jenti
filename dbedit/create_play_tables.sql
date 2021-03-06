
/*USE JENTI;*/

CREATE TABLE `PLAY_IT` (
  `ID` int(15) NOT NULL AUTO_INCREMENT,
  `WORD_ID` int NOT NULL,
  PRIMARY KEY (`ID`)
);

CREATE UNIQUE INDEX PLAY_IT_IDX1 ON PLAY_IT (`ID`, WORD_ID);

INSERT INTO PLAY_IT (WORD_ID)
SELECT DISTINCT WO.ID
FROM WORD WO
WHERE WO.LANGUAGE_CODE = 'it'
AND NOT EXISTS 
(
    SELECT WD.ID
    FROM WORD_DEFINITION WD
    WHERE WD.WORD_ID = WO.ID
    AND (WD.DEFINITION IS NULL
    OR LENGTH(TRIM(WD.DEFINITION)) = 0)
)
ORDER BY 1;


/*
CREATE TABLE `PLAY_IT_ANATOMIA` (
  `ID` int(15) NOT NULL AUTO_INCREMENT,
  `WORD_ID` int NOT NULL,
  PRIMARY KEY (`ID`)
);

INSERT INTO PLAY_IT_ANATOMIA (WORD_ID)
SELECT ID
FROM WORD
WHERE LANGUAGE_CODE = 'it'
AND TAGS LIKE '%(anatomia)%'
ORDER BY 1;
*/


CREATE TABLE `PLAY_EN` (
  `ID` int(15) NOT NULL AUTO_INCREMENT,
  `WORD_ID` int NOT NULL,
  PRIMARY KEY (`ID`)
);

CREATE UNIQUE INDEX PLAY_EN_IDX1 ON PLAY_EN (`ID`, WORD_ID);

INSERT INTO PLAY_EN (WORD_ID)
SELECT DISTINCT WO.ID
FROM WORD WO
WHERE WO.LANGUAGE_CODE = 'en'
AND NOT EXISTS 
(
    SELECT WD.ID
    FROM WORD_DEFINITION WD
    WHERE WD.WORD_ID = WO.ID
    AND (WD.DEFINITION IS NULL
    OR LENGTH(TRIM(WD.DEFINITION)) = 0)
)
ORDER BY 1;
