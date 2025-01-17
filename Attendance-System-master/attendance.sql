
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";



-- Database: `attendance`
CREATE DATABASE IF NOT EXISTS `attendance` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `attendance`;

-- --------------------------------------------------------

-- Table structure for table `objects`

CREATE TABLE IF NOT EXISTS `objects` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_uid` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `year` varchar(10) NOT NULL,
  `section` varchar(50) NOT NULL,
  `object` longblob NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- Table structure for table `teacher`

CREATE TABLE IF NOT EXISTS `teacher` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `email` varchar(25) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `password` varchar(100) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;