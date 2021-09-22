CREATE TABLE `kp_user_token` (
  `user` varchar(8) NOT NULL,
  `token` varchar(32) NOT NULL,
  `generated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `kp_user_token`
  ADD PRIMARY KEY (`user`);