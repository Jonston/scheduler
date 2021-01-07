### Description
I tried to make the task implement based on my understanding.In this example, the doctor has a work schedule (weekly_schedule and daily_schedule tables).It is possible to create a consultation, confirm on both sides and cancel.All the application logic lies in the files app/Services/ConsultationService.php and app/Validators/ConsultationValidator.php.The idea is to break the validation functions into separate fragments for flexible use.I apologize for such meager comments in the code, but I have no right to delay the test back.If you wish, then I can comment on all the functions.The work took about 24 hours.Thank you for your attention.
### Clone repo
``` bash
git clone git@github.com:Jonston/scheduler.git
```
### Install dependencies
``` bash
composer install
```
### Run migrations
```bash
php artisan migrate --seed
```
### Run test
```bash
phpunit
```


