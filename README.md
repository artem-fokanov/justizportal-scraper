# justizportal-scraper
How-to deploy:
1. clone/download code from repository
2. run "php composer.phar install" from the server to download necessary lib's
3. to scrap data you can run from browser parser.php or use command-line script "php console-worker.php"
4. data stores in "db" directory. **please note** that csv file is holds results from the last run. If you want to get the whole data - export it from data.sq3 ([how-to](http://stackoverflow.com/questions/6076984/how-do-i-save-the-result-of-a-query-as-a-csv-file)). Database structure represented by Article and Info tables. "Article" contains plaintext & required fields. "Info" holds keys article_id + article_link to prevent data duplication.
