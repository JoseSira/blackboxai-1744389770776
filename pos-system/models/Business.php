<?php
require_once __DIR__ . '/BaseModel.php';

class Business extends BaseModel {
    protected $table = 'businesses';

    public function __construct() {
        parent::__construct();
    }

    public function createBusiness($data) {
        try {
            // Validate required fields
            $requiredFields = ['name'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field {$field} is required");
                }
            }

            // Create the business with trial subscription
            $businessData = [
                'name' => $data['name'],
                'tax_id' => $data['tax_id'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'subscription_plan' => 'basic',
                'subscription_status' => 'trial',
                'subscription_expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
                'status' => 'active'
            ];

            // Create the business
            $businessId = $this->create($businessData);

            // Create default branch for the business
            $this->createDefaultBranch($businessId, $data['name']);

            return $businessId;
        } catch (Exception $e) {
            throw new Exception("Failed to create business: " . $e->getMessage());
        }
    }

    private function createDefaultBranch($businessId, $businessName) {
        $sql = "INSERT INTO branches (business_id, name, status) VALUES (:business_id, :name, 'active')";
        $params = [
            'business_id' => $businessId,
            'name' => "Sucursal Principal - " . $businessName
        ];
        return $this->query($sql, $params);
    }

    public function getBranches($businessId) {
        return $this->findAll([
            'business_id' => $businessId,
            'status' => 'active'
        ], 'name ASC');
    }

    public function getSubscriptionDetails($businessId) {
        $sql = "SELECT * FROM businesses WHERE id = :business_id";
        $stmt = $this->query($sql, ['business_id' => $businessId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSubscription($businessId, $subscriptionPlan) {
        try {
            // Update business subscription
            $data = [
                'subscription_plan' => $subscriptionPlan,
                'subscription_status' => 'active',
                'subscription_expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
                'status' => 'active'
            ];

            return $this->update($businessId, $data);
        } catch (Exception $e) {
            throw new Exception("Failed to update subscription: " . $e->getMessage());
        }
    }

    public function checkUserLimit($businessId) {
        $sql = "SELECT COUNT(*) as user_count FROM users WHERE business_id = :business_id";
        $stmt = $this->query($sql, ['business_id' => $businessId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Límites por plan
        $limits = [
            'basic' => 5,
            'premium' => 10,
            'enterprise' => -1 // ilimitado
        ];

        // Obtener el plan actual del negocio
        $business = $this->findById($businessId);
        $maxUsers = $limits[$business['subscription_plan']] ?? 5;

        // -1 significa ilimitado
        if ($maxUsers === -1) {
            return true;
        }

        if ($result && $result['user_count'] >= $maxUsers) {
            throw new Exception("User limit reached for current subscription");
        }

        return true;
    }

    public function checkBranchLimit($businessId) {
        $sql = "SELECT COUNT(*) as branch_count FROM branches WHERE business_id = :business_id";
        $stmt = $this->query($sql, ['business_id' => $businessId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Límites por plan
        $limits = [
            'basic' => 3,
            'premium' => 5,
            'enterprise' => -1 // ilimitado
        ];

        // Obtener el plan actual del negocio
        $business = $this->findById($businessId);
        $maxBranches = $limits[$business['subscription_plan']] ?? 3;

        // -1 significa ilimitado
        if ($maxBranches === -1) {
            return true;
        }

        if ($result && $result['branch_count'] >= $maxBranches) {
            throw new Exception("Branch limit reached for current subscription");
        }

        return true;
    }

    public function getBusinessStats($businessId) {
        // Get total sales for today
        $sql = "SELECT COALESCE(SUM(total_amount), 0) as total_sales 
                FROM sales 
                WHERE business_id = :business_id 
                AND DATE(created_at) = CURDATE()";
        $stmt = $this->query($sql, ['business_id' => $businessId]);
        $todaySales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'];

        // Get total products
        $sql = "SELECT COUNT(*) as total_products 
                FROM products 
                WHERE business_id = :business_id 
                AND status = 'active'";
        $stmt = $this->query($sql, ['business_id' => $businessId]);
        $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

        // Get low stock alerts
        $sql = "SELECT COUNT(*) as low_stock_count 
                FROM products 
                WHERE business_id = :business_id 
                AND current_stock <= min_stock 
                AND status = 'active'";
        $stmt = $this->query($sql, ['business_id' => $businessId]);
        $lowStockCount = $stmt->fetch(PDO::FETCH_ASSOC)['low_stock_count'];

        // Get total customers
        $sql = "SELECT COUNT(*) as total_customers 
                FROM customers 
                WHERE business_id = :business_id";
        $stmt = $this->query($sql, ['business_id' => $businessId]);
        $totalCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['total_customers'];

        return [
            'today_sales' => $todaySales,
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'total_customers' => $totalCustomers
        ];
    }
}
?>
