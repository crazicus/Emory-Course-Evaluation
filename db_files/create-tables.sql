CREATE DATABASE eval;

USE eval;

CREATE TABLE student # done
(	studentID CHAR(16) NOT NULL,
	CONSTRAINT studentPK PRIMARY KEY(studentID)
);

CREATE TABLE instructor # done
(	instructorID CHAR(5) NOT NULL,
	fname NVARCHAR(32),
	lname NVARCHAR(32),
	CONSTRAINT instructorPK PRIMARY KEY(instructorID)
);

CREATE TABLE course # done
(	courseID INTEGER(4) UNSIGNED NOT NULL,
	name VARCHAR(10),
	section INTEGER(2) UNSIGNED,
	year YEAR,
	semester VARCHAR(6),
	taughtBy CHAR(5) NOT NULL,
	CONSTRAINT coursePK PRIMARY KEY(courseID),
	CONSTRAINT tbFK FOREIGN KEY(taughtBy) REFERENCES instructor(instructorID)
);

CREATE TABLE takes # done
(	sID CHAR(16) NOT NULL,
	cID INTEGER(4) UNSIGNED NOT NULL,
	CONSTRAINT tsFK FOREIGN KEY(sID) REFERENCES student(studentID),
	CONSTRAINT tcFK FOREIGN KEY(cID) REFERENCES course(courseID)
);

CREATE TABLE question # done
(	questionID INTEGER(4) UNSIGNED NOT NULL AUTO_INCREMENT,
	type CHAR(4),
	text VARCHAR(255),
	CONSTRAINT qPK PRIMARY KEY(questionID)
);

CREATE TABLE evaluation # done
(	cID INTEGER(4) UNSIGNED NOT NULL,
	qID INTEGER(4) UNSIGNED NOT NULL,
	CONSTRAINT evalPK PRIMARY KEY(cID, qID),
	CONSTRAINT ecFK FOREIGN KEY(cID) REFERENCES course(courseID),
	CONSTRAINT eqFK FOREIGN KEY(qID) REFERENCES question(questionID)
);

CREATE TABLE completes
(	cID INTEGER(4) UNSIGNED NOT NULL,
	qID INTEGER(4) UNSIGNED NOT NULL,
	sID CHAR(16) NOT NULL,
	qResponse VARCHAR(255),
	CONSTRAINT cpltPK PRIMARY KEY(sID, cID, qID),
	CONSTRAINT csFK FOREIGN KEY(sID) REFERENCES student(studentID),
	CONSTRAINT ccFK FOREIGN KEY(cID) REFERENCES evaluation(cID),
	CONSTRAINT cqFK FOREIGN KEY(qID) REFERENCES evaluation(qID)
);

CREATE TABLE choice # done
(	qID INTEGER(4) UNSIGNED NOT NULL,
	choiceText VARCHAR(32),
	CONSTRAINT chFK FOREIGN KEY(qID) REFERENCES question(questionID)
);