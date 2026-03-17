# Skill: Add a Provider

Providers implement a contract interface and live in `providers/`.

1. Create the provider directory and class:
   ```
   providers/ProviderName/ProviderName.php
   ```

2. Implement the correct contract:
   ```php
   namespace Providers\ProviderName;

   use OneShot\Core\Contracts\Payment; // or Notify, Mail, Storage

   class ProviderName implements Payment
   {
       // implement all interface methods
   }
   ```

3. Register via `app/Config/Services.php`:
   ```php
   public static function payment(bool $getShared = true): \OneShot\Core\Contracts\Payment
   {
       return new \Providers\ProviderName\ProviderName();
   }
   ```

4. Use anywhere:
   ```php
   service('payment')->charge(1000, 'usd');
   ```

## Available Contracts
| Contract  | Interface                          | Methods                                    |
|-----------|------------------------------------|--------------------------------------------|
| Payment   | `OneShot\Core\Contracts\Payment`   | charge, refund, createSubscription, cancel |
| Notify    | `OneShot\Core\Contracts\Notify`    | send($to, $message, $options)              |
| Mail      | `OneShot\Core\Contracts\Mail`      | send($to, $subject, $body, $options)       |
| Storage   | `OneShot\Core\Contracts\Storage`   | upload, delete, url                        |
