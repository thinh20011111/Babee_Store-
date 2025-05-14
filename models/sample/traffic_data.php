<?php
/**
 * Sample traffic data for demonstration purposes
 * Used only when real data is not available (new installation)
 */

/**
 * Get sample daily traffic data for the last 30 days
 * @return array
 */
function getSampleDailyTraffic() {
    $data = [];
    $today = date('Y-m-d');
    
    // Generate data for the last 30 days
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        
        // Generate a random number between 100 and 600, with a slight upward trend for newer dates
        $visitCount = rand(100, 300) + ($i * 10);
        
        // Reduce visits on weekends
        $dayOfWeek = date('N', strtotime($date));
        if ($dayOfWeek >= 6) { // Saturday or Sunday
            $visitCount = (int)($visitCount * 0.7);
        }
        
        // Add some variability to today's data
        if ($date == $today) {
            $visitCount = rand(80, 500);
        }
        
        $data[] = [
            'period' => $date,
            'count' => $visitCount
        ];
    }
    
    return $data;
}

/**
 * Get sample monthly traffic data
 * @return array
 */
function getSampleMonthlyTraffic() {
    $data = [];
    $currentMonth = date('Y-m');
    
    // Generate data for the last 12 months
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        
        // Generate higher numbers for monthly views
        $visitCount = rand(3000, 8000) + ($i * 300);
        
        // Holiday season boost (November - December)
        $monthNum = (int)date('m', strtotime($month));
        if ($monthNum == 11 || $monthNum == 12) {
            $visitCount = (int)($visitCount * 1.3);
        }
        
        // Summer slump (June - August)
        if ($monthNum >= 6 && $monthNum <= 8) {
            $visitCount = (int)($visitCount * 0.8);
        }
        
        $data[] = [
            'period' => $month,
            'count' => $visitCount
        ];
    }
    
    return $data;
}

/**
 * Get sample page visit statistics
 * @return array
 */
function getSampleTopPages() {
    return [
        ['page_url' => '/', 'count' => 3240],
        ['page_url' => '/products?category=clothing', 'count' => 1820],
        ['page_url' => '/products?category=accessories', 'count' => 1260],
        ['page_url' => '/cart', 'count' => 980],
        ['page_url' => '/products/detail?id=15', 'count' => 760],
        ['page_url' => '/user/login', 'count' => 720],
        ['page_url' => '/products/detail?id=23', 'count' => 680],
        ['page_url' => '/products?category=shoes', 'count' => 620],
        ['page_url' => '/checkout', 'count' => 580],
        ['page_url' => '/contact', 'count' => 520],
    ];
}

/**
 * Get sample referrer sources statistics
 * @return array
 */
function getSampleReferringSources() {
    return [
        ['source' => 'Direct', 'count' => 4560],
        ['source' => 'https://google.com', 'count' => 2840],
        ['source' => 'https://facebook.com', 'count' => 1620],
        ['source' => 'https://instagram.com', 'count' => 1240],
        ['source' => 'https://pinterest.com', 'count' => 980],
        ['source' => 'https://twitter.com', 'count' => 760],
        ['source' => 'https://youtube.com', 'count' => 580],
        ['source' => 'https://tiktok.com', 'count' => 420],
        ['source' => 'https://reddit.com', 'count' => 320],
        ['source' => 'https://bing.com', 'count' => 180],
    ];
}
?>