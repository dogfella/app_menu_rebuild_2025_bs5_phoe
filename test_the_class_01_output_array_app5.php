<?php

$host = 'localhost';
$dbname = 'racepadd_notodb_bs5_2024able_light';
$username = 'root';
$password = '';

try{
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$dbConnection = new PDO($dsn, $username, $password);


    // Set PDO attributes for error handling (optional but recommended)
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    echo "Database connection successful!";

    // query to further confirm
    // $stmt = $dbConnection->query("SELECT 1");
    // if ($stmt) {
    //     echo " - Simple query executed successfully.";
    // }

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    // log the error for development
}

class MenuGenerator {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function generateMenu() {
        // Fetch top-level menu items (parent_id is 0)
        $menu = $this->getMenuItems(0);

        // Build submenus recursively
        $this->buildSubMenus($menu);

        // Output the HTML menu (could also return as JSON if needed)
        $this->printMenuBasicHTML($menu);
    }

    public function generateDebuggerArray() {
        // Fetch top-level menu items (parent_id is NULL)
        $menu = $this->getMenuItems(0);

        // Build submenus recursively
        $this->buildSubMenus($menu);

        // Output the HTML menu (could also return as JSON if needed)
        $this->printMenuArray($menu);
    }

    private function getMenuItems($parentId) {
        // $stmt = $this->db->prepare("SELECT * FROM navigation WHERE parent_id = ? ORDER BY menu_order");
        // $stmt->execute([$parentId]);
        /*
        TABD EDIT: Error on statement above.  Edits below.  Original above.  See MD file chat here for reason: app/chatgpt_chats/markdown_files/navigation_menu_chatgptdemo_version/markdown_geniune_chatGPT_navigation_chatgptdemo_2025_july_28.md
        table name was grp_menu_app5
        */

        if (is_null($parentId)) {
            $stmt = $this->db->prepare("SELECT * FROM navigation_master_old WHERE parent_id IS NULL ORDER BY parent_sort_drag");
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("SELECT * FROM navigation_master_old WHERE parent_id = ? ORDER BY parent_sort_drag");
            $stmt->execute([$parentId]);
        }

        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $item = [
                'id' => $row['id'],
                'parent_id' => $row['parent_id'],
                'title' => $row['menu_name'],
                'href' => $row['link'],
                'icon' => $row['icon'],
                'data_attributes' => $row['keywords_menu'],
                'classes' => $row['css_attributes'],
               // 'is_active' => (bool) $row['is_active'],
                'sub_menu' => [] // Placeholder for submenus
            ];
            $items[] = $item;
        }

        return $items;
    }

    private function buildSubMenus(&$menu) {
        foreach ($menu as &$item) {
            if ($this->hasSubMenu($item['id'])) {
                $item['sub_menu'] = $this->getMenuItems($item['id']);
                $this->buildSubMenus($item['sub_menu']); // Recursive call for deeper levels
            }
        }
    }

    private function hasSubMenu($menuItemId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM navigation_master_old WHERE parent_id = ?");
        $stmt->execute([$menuItemId]);
        return $stmt->fetchColumn() > 0;
    }

    private function printMenuBasicHTML($menu) {
        echo '<ul>';
        foreach ($menu as $item) {
            echo '<li>';
            echo '<a href="' . htmlspecialchars($item['href']) . '">' . htmlspecialchars($item['title']) . '</a>';

            if (!empty($item['sub_menu'])) {
                $this->printMenuBasicHTML($item['sub_menu']); // Recursively print submenus
            }

            echo '</li>';
        }
        echo '</ul>';
    }

    private function printMenuArray($menu) {

    echo '<pre>';
    print_r($menu);
    echo '</pre>';

    }

}

// Test the script
$menuGenerator = new MenuGenerator($dbConnection);

// echo the menus for test
echo $menuGenerator->generateMenu();

echo '<pre>';
print_r($menuGenerator);
echo '</pre>';

echo $menuGenerator->generateDebuggerArray();

/*
print_r prints this:

MenuGenerator Object
(
    [db:MenuGenerator:private] => PDO Object
        (
        )

)
*/

?>
