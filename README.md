# Wind change overview
A website that let's users check a selected region's data on wind changes and the region's wind rose chart. Sends user an email on a user defined wind change in a selected region.
# Prerequisites
The system uses two APIs:  
+ Weather API  
+ SendGrid(Email) API  
  
You can acquire keys these APIs for free from these sites:  
+ Weather API: [https://openweathermap.org/api](https://openweathermap.org/api)  
+ SendGrid API: [https://sendgrid.com/en-us/solutions/email-api](https://sendgrid.com/en-us/solutions/email-api)  
  
In the backend files you can also find a mySQL database dump with all the used tables and data named "Projektas.sql" which you can import into phpMyAdmin.  
# Starting the website
To run the project locally use XAMPP to start the Apache and mySQL servers.  
Put the project files in .../xampp/htdocs directly or make a folder and put the files there.  
Then import the given mySQL database dump to a phpMyAdmin database to get all the tables and data.  
Then in your browser visit [http://localhost/<your_created_folder>/frontend/index.php](http://localhost/<your_created_folder>/frontend/index.php).
