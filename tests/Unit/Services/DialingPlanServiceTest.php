<?php

namespace Tests\Unit\Services;

use App\Models\Customer;
use App\Models\DestinationPrefix;
use App\Models\DialingPlan;
use App\Models\DialingPlanRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DialingPlanServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dialing_plan_allows_number_by_default(): void
    {
        $plan = DialingPlan::create([
            'name' => 'Default Allow Plan',
            'default_action' => 'allow',
            'active' => true,
        ]);

        $result = $plan->isNumberAllowed('34612345678');

        $this->assertTrue($result['allowed']);
        $this->assertEquals('default_action', $result['reason']);
    }

    public function test_dialing_plan_denies_number_by_default(): void
    {
        $plan = DialingPlan::create([
            'name' => 'Default Deny Plan',
            'default_action' => 'deny',
            'active' => true,
        ]);

        $result = $plan->isNumberAllowed('34612345678');

        $this->assertFalse($result['allowed']);
        $this->assertEquals('default_action', $result['reason']);
    }

    public function test_dialing_plan_rule_allows_matching_number(): void
    {
        $plan = DialingPlan::create([
            'name' => 'Spain Only Plan',
            'default_action' => 'deny',
            'active' => true,
        ]);

        DialingPlanRule::create([
            'dialing_plan_id' => $plan->id,
            'type' => 'allow',
            'pattern' => '34*',
            'description' => 'Allow Spain',
            'priority' => 100,
            'active' => true,
        ]);

        $result = $plan->isNumberAllowed('34612345678');

        $this->assertTrue($result['allowed']);
        $this->assertEquals('rule_allow', $result['reason']);
    }

    public function test_dialing_plan_rule_denies_matching_number(): void
    {
        $plan = DialingPlan::create([
            'name' => 'No Premium Plan',
            'default_action' => 'allow',
            'active' => true,
        ]);

        DialingPlanRule::create([
            'dialing_plan_id' => $plan->id,
            'type' => 'deny',
            'pattern' => '900*',
            'description' => 'Block premium',
            'priority' => 100,
            'active' => true,
        ]);

        $result = $plan->isNumberAllowed('900123456');

        $this->assertFalse($result['allowed']);
        $this->assertEquals('rule_deny', $result['reason']);
    }

    public function test_dialing_plan_blocks_premium_destinations(): void
    {
        $plan = DialingPlan::create([
            'name' => 'Block Premium Plan',
            'default_action' => 'allow',
            'block_premium' => true,
            'active' => true,
        ]);

        $premiumPrefix = DestinationPrefix::create([
            'prefix' => '906',
            'country_code' => 'ES',
            'country_name' => 'Spain',
            'region' => 'Premium',
            'is_premium' => true,
            'active' => true,
        ]);

        $result = $plan->isNumberAllowed('906123456', $premiumPrefix);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('premium_blocked', $result['reason']);
    }

    public function test_dialing_plan_rule_priority_order(): void
    {
        $plan = DialingPlan::create([
            'name' => 'Priority Test Plan',
            'default_action' => 'deny',
            'active' => true,
        ]);

        // Lower priority number = higher priority (checked first)
        DialingPlanRule::create([
            'dialing_plan_id' => $plan->id,
            'type' => 'deny',
            'pattern' => '346*',
            'description' => 'Deny Spain Mobile',
            'priority' => 50,
            'active' => true,
        ]);

        DialingPlanRule::create([
            'dialing_plan_id' => $plan->id,
            'type' => 'allow',
            'pattern' => '34*',
            'description' => 'Allow all Spain',
            'priority' => 100,
            'active' => true,
        ]);

        // Mobile should be denied (priority 50 checked first)
        $result = $plan->isNumberAllowed('34612345678');
        $this->assertFalse($result['allowed']);

        // Fixed should be allowed (doesn't match 346*, falls through to 34*)
        $result = $plan->isNumberAllowed('34912345678');
        $this->assertTrue($result['allowed']);
    }

    public function test_customer_can_dial_without_plan(): void
    {
        $customer = Customer::factory()->create([
            'active' => true,
            'dialing_plan_id' => null,
        ]);

        $result = $customer->canDialNumber('34612345678');

        $this->assertTrue($result['allowed']);
        $this->assertEquals('no_dialing_plan', $result['reason']);
    }

    public function test_customer_with_plan_restricts_dialing(): void
    {
        $plan = DialingPlan::create([
            'name' => 'Restricted Plan',
            'default_action' => 'deny',
            'active' => true,
        ]);

        DialingPlanRule::create([
            'dialing_plan_id' => $plan->id,
            'type' => 'allow',
            'pattern' => '34*',
            'priority' => 100,
            'active' => true,
        ]);

        $customer = Customer::factory()->create([
            'active' => true,
            'dialing_plan_id' => $plan->id,
        ]);

        // Spain allowed
        $result = $customer->canDialNumber('34612345678');
        $this->assertTrue($result['allowed']);

        // USA denied
        $result = $customer->canDialNumber('14155551234');
        $this->assertFalse($result['allowed']);
    }
}
