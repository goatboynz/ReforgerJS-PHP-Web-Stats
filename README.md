# ReforgerJS Stats Website

This project provides a simple website to display player statistics from your Arma Reforger server, leveraging data saved by ReforgerJS.

## Features

*   **Player Listing:** Displays a paginated list of players.
*   **Player Search:** Allows searching for players by name.
*   **Detailed Stats:** Presents comprehensive statistics for individual players.
*   **Top Player Rankings:** Showcases leaderboards for kills, playtime, K/D ratio, and accuracy.
*   **Database Integration:** Connects to a MySQL database to retrieve player data.
*   **Customizable Appearance:** Allows customization of the main color and server name.
*   **Discord Link:** Provides a floating Discord icon linking to your server.

## Screen Shots
** Top 10 **
![Alt text](https://i.imgur.com/pOM9O5X.png) 
** Players / Search **
![Alt text](https://i.imgur.com/Vhw3bCb.png) 
** Player Stats **
![Alt text](https://i.imgur.com/Og1NL0f.png) 


## Prerequisites

*   **Web Server:** A web server (e.g., Apache, Nginx) with PHP support.
*   **PHP:** PHP version 7.0 or higher.
*   **MySQL Database:** A MySQL database server (e.g., MySQL, MariaDB).
*   **ReforgerJS:**  A ReforgerJS setup that saves player data to the configured MySQL database.

## Installation

1.  **Clone/Download the Repository:**

    Download the contents of this repository to your local machine or server.

2.  **Database Setup:**

    *   Ensure you have a MySQL database set up and running.
    *   The database must be populated with data from ReforgerJS. ReforgerJS must be configured to save the player data to this database.  Refer to the ReforgerJS documentation for details on database setup and configuration.

3.  **Configuration:**

    *   Open the `index.php` file in a text editor.
    *   Locate the following configuration section:

        ```php
        // --- DATABASE CONFIGURATION ---
        $dbHost = "localhost:3306";
        $dbUser = "USERNAME";
        $dbPass = "PASSWORD";
        $dbName = "DBNAME";
        // -----------------------------

        // --- OTHER CONFIGURATION ---
        $servername = "YOUR SERVER NAME";
        $mainColor = "#e94560";
        $discordIcon = YOUR DISCORD ICON URL
        $discordUrl= YOUR DISCORD URL
        $playersPerPage = 50;
        // ----------------------------------
        ```

    *   **Database Configuration:**
        *   `$dbHost`: Set this to your MySQL database host address (e.g., "localhost:3306").
        *   `$dbUser`: Set this to your MySQL database username.
        *   `$dbPass`: Set this to your MySQL database password.
        *   `$dbName`: Set this to the name of your MySQL database.

    *   **Other Configuration:**
        *   `$servername`: Set this to the name of your Arma Reforger server.  This will be displayed on the website.
        *   `$mainColor`:  Set this to the primary color you want to use for the website's theme.  Use a valid hex color code (e.g., "#e94560").
        *   `$discordIcon`: Set this to the URL of your Discord icon. The website includes a discord icon link in the lower right.
        *   `$discordUrl`: Set this to the URL of your Discord server invite link.
        *   `$playersPerPage`: Set this to the number of players you want to display per page on the player list.

4.  **Web Server Configuration:**

    *   Place the `index.php` file in your web server's document root directory (e.g., `/var/www/html/`).
    *   Ensure your web server is configured to execute PHP files.

5.  **Access the Website:**

    *   Open a web browser and navigate to the URL where you placed the `index.php` file (e.g., `http://yourserver.com/index.php`).

## Usage

*   **Browsing Players:** The main page displays a list of players, paginated according to the `$playersPerPage` setting.
*   **Searching Players:** Use the search box to find players by name.  The search is not case-sensitive.  You must type at least 3 characters to initiate a search.
*   **Viewing Player Stats:** Click on a player's card to view their detailed statistics.
*   **Pagination:** Use the "More Players" button to load the next page of player listings.
*   **Back Button:** When viewing player stats, click the "Back to Players" button to return to the player list.
*   **Discord:** Click on the goat with a discord logo in the bottom right to join the discord server.

## Troubleshooting

*   **Database Connection Errors:**
    *   Verify that your database credentials in `index.php` are correct.
    *   Ensure that the MySQL server is running and accessible from your web server.
    *   Check the MySQL user's permissions to ensure they have the necessary privileges to access the database.

*   **No Data Displayed:**
    *   Verify that your ReforgerJS setup is correctly saving data to the configured MySQL database.
    *   Check the database tables to ensure that they contain data.
    *   Examine the SQL queries in `index.php` to ensure they are correctly retrieving data from the database.

*   **Website Not Loading:**
    *   Check your web server's configuration to ensure that it is correctly serving PHP files.
    *   Examine your web server's error logs for any PHP errors.

## Contributing

Contributions are welcome! Please submit pull requests with bug fixes, new features, or improvements to the documentation.
