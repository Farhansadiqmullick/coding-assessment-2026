<?php

/**
 * Invoice Class
 *
 * Handles invoice creation and management
 * Started: 2 weeks ago
 * Last modified: Friday (was in a hurry)
 */
class Invoice
{

    private $customer;
    private $items = [];
    private $discount = 0;
    private $id;
    private $createdAt;

    public function __construct($customerName)
    {
        $this->customer = $customerName;
        $this->id = time(); // Not sure if this is the best approach...
        $this->createdAt = date('Y-m-d H:i:s');
    }

    /**
     * Add an item to the invoice
     * Note: Make sure to use consistent naming!
     */
    public function addItem($name, $price, $quantity)
    {
        // --- Name validation ---
        $name = trim((string) $name);

        if ($name === '') {
            throw new InvalidArgumentException('Item name cannot be empty');
        }

        // --- Price validation ---
        if (!is_numeric($price)) {
            throw new InvalidArgumentException('Item price must be numeric');
        }

        $price = round((float) $price, 2);

        if ($price <= 0) {
            throw new InvalidArgumentException('Item price must be greater than zero');
        }

        // --- Quantity validation ---
        if (!is_numeric($quantity) || (int) $quantity != $quantity) {
            throw new InvalidArgumentException('Item quantity must be an integer');
        }

        $quantity = (int) $quantity;

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Item quantity must be greater than zero');
        }

        // --- Add item ---
        $this->items[] = [
            'name' => $name,
            'price' => $price,
            'qty'   => $quantity
        ];
    }

    /**
     * Calculate total
     * BUG: This doesn't match up with addItem() - need to fix
     */
    public function getTotal()
    {
        $total = 0;
        foreach ($this->items as $item) {
            // Accessing 'quantity' but we stored it as 'qty'!
            $total += $item['price'] * $item['qty'];
        }
        return $total - $this->discount;
    }

    /**
     * Apply discount to invoice
     * TODO: Should discounts apply before or after tax?
     * TODO: Client hasn't decided on the business rules yet
     */
    public function applyDiscount($percent)
    {
        // Started implementing but not sure about requirements
        // throw new Exception("Not implemented - waiting on client clarification");

        // Trying basic implementation but commented out until we get clarity
        try {
            // Basic validation
            if (!is_numeric($percent)) {
                throw new \InvalidArgumentException('Discount percent must be a number.');
            }

            if ($percent <= 0 || $percent > 100) {
                throw new \InvalidArgumentException('Discount percent must be between 1 and 100.');
            }

            $subtotal = $this->getTotal();

            if ($subtotal <= 0) {
                throw new \RuntimeException('Cannot apply discount on empty or zero total.');
            }

            // Apply discount
            $this->discount = $subtotal * ($percent / 100);

            return $this->discount;
        } catch (\Throwable $e) {
            // Log if needed
            // error_log($e->getMessage());

            // Re-throw or handle gracefully
            throw new \Exception(
                'Failed to apply discount: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Get invoice ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get customer name
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Get items array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Convert invoice to array for JSON serialization
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'customer' => $this->customer,
            'items' => $this->items,
            'discount' => $this->discount,
            'total' => $this->getTotal(),
            'created_at' => $this->createdAt
        ];
    }

    /**
     * Save invoice to file
     * FIXME: This overwrites everything! Need to fix but running out of time
     * Should APPEND to the file, not replace it
     */
    public function saveToFile($filename = 'data/invoices.json')
    {

        $invoice = $this->toArray();

        // Load existing data
        if (file_exists($filename)) {

            $contents =  file_get_contents($filename);
            $repairedJson = JsonInvoiceRepair::repairIfNeeded(
                $contents,
                dirname($filename) . '/invoices.repair.log'
            );

            $existingData = json_decode($repairedJson, true);

            // If single invoice → wrap
            if (isset($existingData['id'])) {
                $existingData = [$existingData];
            }

            // If nested array (caused by repair)
            if (is_array($existingData) && count($existingData) === 1 && is_array($existingData[0])) {
                if (isset($existingData[0][0])) {
                    $existingData = $existingData[0];
                }
            }

            if (!is_array($existingData)) {
                $existingData = [];
            }
        } else {
            $existingData = [];
        }

        // Append new invoice
        $existingData[] = $invoice;

        // Save back to file
        file_put_contents(
            $filename,
            json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );

        return true;
    }

    /**
     * Load invoice from file by ID
     * Started this but didn't finish testing it
     */
    public static function loadFromFile($id, $filename = 'data/invoices.json')
    {
        if (!file_exists($filename)) {
            throw new Exception("Invoice file not found");
        }

        $contents = file_get_contents($filename);

        //Repair the json corrupted data if needed
        $repairedJson = JsonInvoiceRepair::repairIfNeeded(
            $contents,
            dirname($filename) . '/invoices.repair.log'
        );

        $invoices = json_decode($repairedJson, true);

        // Handle both single invoice and array of invoices
        // (since saveToFile is broken and only saves one)
        if (isset($invoices['id'])) {
            $invoices = [$invoices];
        }

        foreach ($invoices as $invoiceData) {
            if ($invoiceData['id'] == $id) {
                $invoice = new Invoice($invoiceData['customer']);
                $invoice->id = $invoiceData['id'];
                $invoice->discount = $invoiceData['discount'];

                foreach ($invoiceData['items'] as $item) {
                    // After validation qty should come first, for the fallback we have quantity
                    $qty = isset($item['qty']) ? $item['qty'] : $item['quanity'];
                    $invoice->addItem($item['name'], $item['price'], $qty);
                }

                return $invoice;
            }
        }

        throw new Exception("Invoice not found: " . $id);
    }
}
