<?php
function showAlert($message) {
    echo "<script>alert('" . htmlspecialchars($message, ENT_QUOTES) . "');</script>";
}

// Example usage:
if (isset($_GET['message'])) {
    showAlert($_GET['message']);
}

// Alternative method using JavaScript
function jsAlert($message) {
    return "<div class='alert' onclick='this.style.display=\"none\";'>
            $message
            <span class='close-btn'>&times;</span>
           </div>
           <style>
           .alert {
               padding: 20px;
               background-color: #f44336;
               color: white;
               margin-bottom: 15px;
               position: relative;
           }
           .close-btn {
               cursor: pointer;
               position: absolute;
               right: 15px;
           }
           </style>";
}
?>