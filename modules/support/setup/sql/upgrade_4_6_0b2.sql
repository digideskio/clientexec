UPDATE `setting` SET `description` = 'Enter the message you would like entered into the ticket when it is closed.<br>Template Tags: [CLIENTNAME], [TICKETNUMBER], [TICKETSUBJECT], [TICKETFIRSTLOG], [COMPANYNAME]', `value` = "ATTN: [CLIENTNAME],\r\n\r\nYour Support Ticket #[TICKETNUMBER] with subject \"[TICKETSUBJECT]\" has been closed due to inactivity.\r\nIf this issue has not been resolved please reopen this ticket.\r\n\r\nThank you,\r\n[COMPANYNAME]" WHERE `name` LIKE 'plugin_autoclose_Ticket Message';