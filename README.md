# justizportal-scraper
How-to deploy:
1. clone/download code from repository
1. get into a code directory "cd /path/to/code/directory"
1. run "php composer.phar install" to download necessary lib's
1. to scrap data you can run from browser parser.php or use command-line script "php console-worker.php"
1. data stores in "db" directory. Database structure represented by Article and Info tables. "Article" contains plaintext & required fields. "Info" holds keys article_id + article_link to prevent data duplication.
1. You can specify "Registerant" option from command-line. Available values are: HRA, HRB. Following examples are equal: 
    1. ```php console-worker.php -r=HRA```
    1. ```php console-worker.php --registerant=HRA```
