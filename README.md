# Setup

```sh
git clone https://github.com/KrisDriv/sunfinance-task 
cd sunfinance
composer install 
```

### Configure

Create `.env` file from `.env.example` and fill database credentials. Due to limitations of ORM I chose,
I was limited to using only MySQL.

### Create Tables

Execute `./console migrate`

### Populate Tables

```
./console import:customer resources/json/customers.json
./console import:loan resources/json/loan.json
```

Some entities will fail to import, and that is expected (I suppose). To see why - increase verbosity. eg:

```
./console import:customer resources/json/customers.json -v
```

# Application In-Action

### Import Payments

```
./console import:payment resources/import/payments.csv
```

Unlike the other imports, this will not just go through straight to database. Payment will be properly processed
and even refunds issued. This will also fire appropriate events which will be captured in `App\Listeners\PaymentListener`
from which then can be used for communication.

### Report

// TODO

### Posted Payments

// TODO

# Tests

I did write two test files for EntityHydrateService and just to test if Test environment is properly setup.
Those were the easiest ones, I began with those just to showcase that it is in my possibilities.
There might be more, I'll see how much free time I can allocate for that.

Run tests:
```
./vendor/bin/phpunit
```

## P.S.
There are way more that was done. It did expand quite a bit. Arguably not necessary features, but I decided to do those
anyway. If Your evaluation of my task is below the threshold you are looking for, I at least enjoyed doing it.

I hope that you will take your time to explore it and I will appreciate that. Cheers!