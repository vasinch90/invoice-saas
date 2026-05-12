<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $subtotal  = fake()->randomFloat(2, 1000, 100000);
        $taxRate   = 7;
        $taxAmount = $subtotal * ($taxRate / 100);

        return [
            'user_id'        => \App\Models\User::factory(),
            'client_id'      => \App\Models\Client::factory(),
            'invoice_number' => 'INV-' . fake()->unique()->numerify('####'),
            'status'         => fake()->randomElement(['draft', 'sent', 'paid']),
            'issue_date' => fake()->dateTimeBetween('first day of january', 'last day of december'),
            'due_date'   => fake()->dateTimeBetween('first day of january', 'last day of december'),
            'subtotal'       => $subtotal,
            'tax_rate'       => $taxRate,
            'tax_amount'     => $taxAmount,
            'total'          => $subtotal + $taxAmount,
            'notes'          => fake()->sentence(),
        ];
    }
}