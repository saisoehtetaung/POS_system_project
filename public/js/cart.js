$(document).ready(function() {
            function setQtyPrice(e) {
                $parentNode = e.parents('tr');
                $price = Number($parentNode.find('#pizzaPrice').text().replace('kyats', ''));
                $qty = Number($parentNode.find('#qty').val());

                $total = $price * $qty;
                $parentNode.find('#total').html($total +
                    " kyats");
            }

            function getsubtotal() {
                $subTotal = 0;
                $('#dataTable tr').each(function(index, element) {
                    $subTotal += Number($(element).find('#total').text().replace('kyats', ''));
                });

                $('#subTotalPrice').html($subTotal + " kyats");
                $('#finalPrice').html(`${$subTotal + 3000} kyats`);
            }

            $('.btn-plus').click(function() {

                setQtyPrice($(this));
                getsubtotal();

            });

            $('.btn-minus').click(function() {
                setQtyPrice($(this));
                getsubtotal();
            });

        });
