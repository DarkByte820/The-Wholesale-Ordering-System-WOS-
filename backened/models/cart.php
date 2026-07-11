<?php

class Cart {

    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }


   public function addToCart($UserID, $product_id, $quantity, $item_type, $package_id, $added_at)
{
    $sql = "
        INSERT INTO cart
        (UserID, product_id, quantity, item_type, package_id, added_at)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        quantity = quantity + VALUES(quantity)
    ";

    $stmt = $this->db->prepare($sql);

    if (!$stmt) {
        die("SQL Error: " . $this->db->error);
    }
echo "UserID: $UserID, Product ID: $product_id, Quantity: $quantity, Item Type: $item_type, Package ID: $package_id, Added At: $added_at";
    $stmt->bind_param(
        "iiisis",
        $UserID,
        $product_id,
        $quantity,
        $item_type,
        $package_id,
        $added_at
    );

    return $stmt->execute();
}



    public function getCart($user_id)
    {

        $sql = "
            SELECT
                cart_id,
                p.Product_id,
                quantity,
                item_type,
                package_id,
                added_at

            FROM cart c

            INNER JOIN products p
            ON c.product_id = p.Product_id

            WHERE c.UserID = ?
        ";


        $stmt = $this->db->prepare($sql);


        if (!$stmt) {
            die("SQL Error: " . $this->db->error);
        }


        $stmt->bind_param(
            "i",
            $user_id
        );


        $stmt->execute();


        return $stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);
    }



    public function updateQuantity($cart_id, $quantity)
    {

        $stmt = $this->db->prepare(
            "UPDATE cart 
             SET Quantity = ?
             WHERE CartID = ?"
        );


        $stmt->bind_param(
            "ii",
            $quantity,
            $cart_id
        );


        return $stmt->execute();
    }




    public function removeFromCart($cart_id)
    {

        $stmt = $this->db->prepare(
            "DELETE FROM cart WHERE CartID = ?"
        );


        $stmt->bind_param(
            "i",
            $cart_id
        );


        return $stmt->execute();
    }




    public function clearCart($user_id)
    {

        $stmt = $this->db->prepare(
            "DELETE FROM cart WHERE UserID = ?"
        );


        $stmt->bind_param(
            "i",
            $user_id
        );


        return $stmt->execute();
    }




    public function getCartTotal($user_id)
    {

        $stmt = $this->db->prepare(
            "
            SELECT SUM(c.quantity * p.Unit_price) AS total

            FROM cart c

            INNER JOIN products p
            ON c.product_id = p.Product_id

            WHERE c.UserID = ?
            "
        );


        $stmt->bind_param(
            "i",
            $user_id
        );


        $stmt->execute();


        $result = $stmt
            ->get_result()
            ->fetch_assoc();


        return $result['total'] ?? 0;
    }

}

?>