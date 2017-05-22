# justizportal-scraper
How-to deploy:
1. clone/download code from repository
1. get into a code directory "cd /path/to/code/directory"
1. run "php composer.phar install" to download necessary lib's
1. to scrap data you can run from browser parser.php or use command-line script "php console-worker.php"
1. data stores in "db" directory. **please note** that csv file is holds results from the last run. If you want to get the whole data - export it from data.sq3 ([how-to](http://stackoverflow.com/questions/6076984/how-do-i-save-the-result-of-a-query-as-a-csv-file)). Database structure represented by Article and Info tables. "Article" contains plaintext & required fields. "Info" holds keys article_id + article_link to prevent data duplication.
