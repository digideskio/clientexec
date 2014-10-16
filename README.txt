*********************
Upgrading ClientExec
*********************
READ the CHANGELOG.txt file

1) First step in any upgrade is to backup your database.  You can do so using an application like phpmyadmin which is commonly found with your hosting control panel utilities.
NOTE: We can not stress this step enough. We can not help you easily with any issues to your data unless you have a backup before any upgrade.
2) Unzip the contents of your compressed ClientExec file.
3) FTP to your server, which contains the domain of your ClientExec installation.
4) Remove all of your files except for:
   config.php
   uploads - Do not remove this folder if you have files in your files manager or attached to support tickets.
   * clear out uploads/cache
5) Upload all the content files from the ClientExec zip file in binary mode. Ensure that you do not overwrite config.php
6) Visit http://yourceurl/install.php and click on Upgrade.  Follow the steps until completion.

Upgrading Notes:
-Note: Please take a look at your SupportPipe.php file.  If your previous file contained a hashband as the first line, please copy that line to your new SupportPipe.php
https://www.clientexec.com/members/index.php?fuse=knowledgebase&view=article&controller=articles&articleId=61
-If you see the install option available when you run the install.php script then you need to check your config.php and ensure that you didn't overwrite by mistake.
-If after upgrading you find that you have unauthorized version installed message do not stress, just follow the instructions below on obtaining a new license.
-Ensure that the uploads/support and uploads/files directory are writable if you intend on allowing attachments to support tickets or using the files module.

*********************
Initial Installation
*********************

1) Unzip the contents of your compressed ClientExec file
2) FTP to your server and upload all the content files from the ClientExec zip file in binary mode.
3) Visit http://yourceurl/install.php and click on Install.  Follow the steps until completion.

Fresh Installation Notes:
- Ensure that the uploads directories are writable if you intend on allowing attachments to support tickets or using the files module.

******************
Obtaining Support
******************

You can submit support tickets at support@clientexec.com
Forum access is now open to all ClientExec users at http://forum.clientexec.com/
Please search for possible answers to your questions before posting.


*****************
Troubleshooting
*****************

Chat not working
----------------
If you're using Apache and are compressing all content (in CPanel, when "Optimize Website" is set to "Compress all content") then rename htaccess.txt to .htaccess
This will avoid compressing the chat stream.

