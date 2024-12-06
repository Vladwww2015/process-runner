## Usage
### 1) Create di.xml

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">

    <virtualType name="Example\SomeExtension\Model\ProcessMultirun" type="IntegrationHelper\ProcessRunner\Model\Process">
        <arguments>
            <argument name="isMultiProcess" xsi:type="boolean">true</argument>
            <argument name="multiProcessCount" xsi:type="string">10</argument>
        </arguments>
    </virtualType>
    <virtualType name="Example\SomeExtension\Model\ProcessSinglerun" type="IntegrationHelper\ProcessRunner\Model\Process">
    </virtualType>
    <type name="IntegrationHelper\ProcessRunner\Model\ProcessPool">
        <arguments>
            <argument name="processes" xsi:type="array">
                <item name="some_process_multi" xsi:type="object">IntegrationHelper\SomeExtension\Model\ProcessMultirun</item>
                <item name="some_process_single" xsi:type="object">IntegrationHelper\SomeExtension\Model\ProcessSinglerun</item>
            </argument>
        </arguments>
    </type>
</config>
```

### 2) Save yours profile

```php
use IntegrationHelper\ProcessRunner\Model\ProcessProfileManager
use Example\SomeExtension\Model\RunImportProfile

class ClassName {
    public function __construct(
        ...
        protected ProcessProfileManager $processProfileManager
    ){}
    
    private function runMultiProcessing()
    {
        $result = [...,...,...];
        $total = count($result);

        $batchSize = ceil($total / 50);
        $previousBatchSize = 1;
        $i = 1;
        while ($i <= 50) {
            $batch = sprintf('%s-%s', $previousBatchSize, $i*$batchSize);
            $previousBatchSize = $i*$batchSize;
            $code = sprintf('%s-%s', ConstraintsInterface::CURRENT_PROFILE_CODE, $batch);
            $data = [
                \IntegrationHelper\ProcessRunner\Api\ConstraintsInterface::IDENTITY => 'unique_profile_name_here',
                \IntegrationHelper\ProcessRunner\Api\ConstraintsInterface::CODE => $code,
                \IntegrationHelper\ProcessRunner\Api\ConstraintsInterface::MODEL => RunImportProfile::class,
                \IntegrationHelper\ProcessRunner\Api\ConstraintsInterface::MODEL_METHOD_ARGUMENTS => json_encode(
                    [
                        'profile' => 'unique_profile_name_here',
                        'batch' => $batch
                    ]
                )
            ];
            $i++;
            $this->processProfileManager->create($data);
        }
    }
}

```

### 3) Implement IntegrationHelper\ProcessRunner\Model\ModelInstanceInterface in yours profile Model

```php
namespace Example\SomeExtension\Model;

class RunImportProfile implements \IntegrationHelper\ProcessRunner\Model\ModelInstanceInterface
{
    public function runProcess(array $params = []): bool
    {
        $profile = $params['profile'] ?? false;
        $batch = $params['batch'] ?? false;
        
        if(!$profile) return;

        $result = false;
        try {
            // Code here
        } catch (\Exception $e) {
            $result = true;
        }

        return !$result;
    }
}


```
