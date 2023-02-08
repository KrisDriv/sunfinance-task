## Import Sequence

### 1. Receive Raw Entity array

```php
array(
    'id' => '000f-000f-000f-000f', // UUID
    'field_one' => 'Value One', // string & varchar
    'field_two' => 2, // integer
    'field_three' => 'Paid' // Enum,
    'field_four' => 'Contract nr. is 304' // To be transformed
    'field_five' => '6th of September, 2005' // Cast to DateTimeInterface object
    // ...
);
```

*
  1. Raw content will be retrieved via Http Post request or read from files.
  2. This could be form request, correcting user-error will be done at step 2.

### 2. Re-mapping (Translating keys)
Raw data structure will vary based on source. For that reason we must re-structure the array. 
See file [EntityImportConfig](./src/Config/EntityImportConfig.php). Mappings are assigned to Entity class.
Left side is incoming raw data keys where the right side is final key that will be returned.

#### Example
[EntityImportConfig::KEY_TRANSLATIONS]()
```php
$translations = array(
    'identification' => 'id',
);

$data = [
    'identification' => 4,
    'lastName' => 'Lazar',
    'first_name' => 'Bob'
]

$EntityHydrateService->translateKeys($data, $translations);

# Output:
array(
    'id' => 4,
    'last_name' => 'Bob',
    'first_name' => 'Lazar'
);
```

In case of no translation entry, the raw key will be snake-cased to conform to Entity class property naming convention.

**Note: Duplicates on the right side will be overwritten in the final array by the latter definition.**
 
  1. Create new array
  2. Loop over raw 
  3. Find translation key
      1. case of no translation entry: snake case original key
  4. Pair translated key with original key data

It is expected that translated keys are always in the snake case format. 
If your translation keys configuration does not adhere to this expectation - missing key errors will be thrown in hydration step.
By the end of this process any reference to the fields must be use the translated keys.

### 2. Transform Data
```php
'field_four' => 'Contract nr. is 304' // To be transformed
```
In this step we examine incoming data and dynamically extract the data we are interested in.

To do this we must call [EntityHydrateService::passThrough()]() method and give appropriate layers. 
In Payments example we use [PaymentTransformationLayer](./src/Import/Layers/PaymentTransformationLayer.php)

```php
$row = [
    'id' => 6,
    'last_name' => 'franko',
    'first_name' => 'Eddie  '
    'social_security_number' => '1NV4L1D'
];

$EntityHydrateService->passThrough(
    $row,
    new \App\Import\Layers\PaymentTransformationLayer()
)
```
Code above will make following calls

1. _PaymentTransformationLayer::transformId()_
2. _PaymentTransformationLayer::transformLastName()_
3. _PaymentTransformationLayer::transformFirstName()_

Only if these methods are defined within the layer and if Layer is using DynamicFieldClass trait
Otherwise three calls to _PaymentTransformationLayer::transformField()_ will be made

**Results of these calls will persist in the give row.**

Result of this _passThrough()_ call might look something like this

```php
array(
    'id' => 6,
    'last_name' => 'Franko', // Notice uppercase
    'first_name' => 'Eddie' // Notice trimmed spaces,
)
```

Failed transformations will throw [ImportTransformException](./src/Exceptions/Import/ImportTransformException.php)

### 3. Validate
Just like in step two. However, this time [PaymentValidationLayer](./src/Import/Layers/PaymentValidationLayer.php) 
will be used. And different to previous step calls to these method will return a multidimensional array of errors 
associated with each field.


Example using same data as in step two:

```php
// Success:
array()

// Error
array(
    'id' => array(1) (
        object(\App\Exceptions\Import\ImportValidationException::class) {
            'message' => 'Duplicate',
            'isDuplicate' => true,
            // ...
        },
    ),
    'social_security_number' => array(1) (
        object(\App\Exceptions\Import\ImportValidationException::class) {
            'message' => "Could not read a valid social security number from value '1NV4L1D'",
            // ...
        }
    )
)
```

### 4. Hydrate
Imported raw data must be cast to Entity class for further processing.

### 5. Process

### 6. Save

## Layers
