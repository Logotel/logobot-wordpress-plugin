=== Logobot Wordpress Plugin ===
Tags: logobot, chatbot
Requires at least: 5.9.8
Tested up to: 6.6.1

== Installation ==
Installing the plugin is easy, just follow the standard Wordpress procedure: 
- uploading the zip archive via Plugins > Add New Plugin > Upload Plugin
- or by uploading the ‘logobot-wp’ folder via ftp directly into the wp-content/plugins folder.

Once installed and activated, you need to configure it.
Create a new folder ‘logotel-wp’ in the wordpress uploads folder. In the newly created path ‘wp-content/uploads/logotel-wp’ add a privatekey.pem file with the personal private key you received to be able to use the Logobot.
Open the menu item ‘Logobot’ in the wordpress main menu (only visible to the Admin) and enter the required information:
- Private key: the system checks for the presence of the file privatekey.pem
- License: enter your personal licence key
- Logobot Client: select whether you want to use the staging or production logobot
- Bot name: write the custom name you want to give the Logobot

Conclude the configuration, remembering to select the ‘Activate’ checkbox.

At this point the Logobot is fully active and you can instantiate it on any page or post via the wordpress editor by selecting the ‘Logobot’ block in the Widget category.

== Changelog ==
0.1.0
First beta version!