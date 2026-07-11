<?php


class CartController {


    public static function getCart()
    {

        $user = authenticateUser();


        $cart = new Cart();


        $items = $cart->getCart(
            $user['userId']
        );


        $total = $cart->getCartTotal(
            $user['userId']
        );


        Response::success([
            "items"=>$items,
            "total"=>$total,
            "itemCount"=>count($items)

        ], "Cart retrieved");

    }



    public static function addToCart()
    {

        $user = authenticateUser();


        $input=json_decode(
            file_get_contents("php://input"),
            true
        );


        if(
            empty($input['product_id']) ||
            empty($input['quantity'])
        ){

            Response::error(
                "Product ID and quantity required",
                BAD_REQUEST
            );

        }



        $cart=new Cart();



        if(
            $cart->addToCart(
                $user['userId'],
                $input['product_id'],
                $input['quantity'],
                $input['item_type'] ?? 'product',
                $input['package_id'] ?? null,
                date('Y-m-d H:i:s')
            )
        ){

            Response::success(
                null,
                "Item added to cart",
                CREATED
            );

        }


        Response::error(
            "Unable to add item",
            SERVER_ERROR
        );

    }





    public static function removeFromCart()
    {

        $user=authenticateUser();


        $input=json_decode(
            file_get_contents("php://input"),
            true
        );


        $cart=new Cart();


        if(
            $cart->removeFromCart(
                $input['cartId']
            )
        ){

            Response::success(
                null,
                "Removed from cart"
            );

        }


        Response::error(
            "Failed",
            SERVER_ERROR
        );

    }



}

?>