<?php
  require_once("../includes/session.php");
  require_once("../includes/navigation.php");
  require_once("../includes/form_processing.php");
  require_once("../includes/dbconnection.php");
  if(is_buyer()) {
      include("../includes/layouts/header.php");
    } else {
      redirect_to("index.php");
    }
  $userId = $_SESSION['userId'];
if(isset($_GET['auctionId']) && !empty($_GET['auctionId']))
  unfavoriteAuction();
?>

<div class="container">
  <h1>My Account</h1>
  <p>Welcome to your personal area. Here you can check your personal details as well as monitor auctions you have bid for.</p>

<div class="row">
  <div class="col-md-2 menu-margin">
    <ul class="nav nav-list">
      <li>
        <a href="#address">
          Address
        </a>
      </li>

      <li>
        <a href="#bids">
          Bids
        </a>
      </li>

      <li>
        <a href="#following">
          Following
        </a>
      </li>

      <li>
        <a href="#awarded-auctions">
          Awarded
        </a>
      </li>

    </ul>
  </div>


  <div class="col-md-10">
    <?php
    $average_stars_user = getAverageStarsForUser($userId);

      $auction_set_unfiltered = retrieve_buyer_auctions();
      $auction_set = filter_non_expired_auctions($auction_set_unfiltered);
      $auction_set = filter_auctions_not_won($auction_set, $_SESSION['userId']);
      $auction_set = filter_auctions_already_rated($auction_set, $_SESSION['role']);
      if($auction_set) {
        $output = "
        <div class=\"alert alert-warning\" role=\"alert\">
          <button type=\"button\" class=\"close fui-cross\"
              data-dismiss=\"alert\">
          </button>
          <h4>Leave Feedback!</h4>
          <table class=\"table\" id=\"table-account-feedback\">
            <col width=\"200px\">";
        foreach ($auction_set as $auction) {
          $encoded_seller_id  = urlencode(htmlentities($auction['seller']));
          $encoded_auction_id = urlencode(htmlentities($auction['auctionId']));
          $imageName      = htmlentities($auction['imageName']);
          $title          = htmlentities($auction['title']);
          $description    = htmlentities($auction['description']);
          $winning_price  = htmlentities($auction['winning_price']);
          $link  = "leave_feedback.php?user_id={$encoded_seller_id}";
          $link .= "&auction_id={$encoded_auction_id}";
          $output .= "<tr>
                      <td><a href=\"{$link}\"><h7>{$title}</h7>
                      <img src=\"img/auctions/{$imageName}\"
                      title=\"{$title}\">
                      </a></td>
                      <td>
                        {$auction['description']}
                      </td>
                      <td>
                        <Strong>Bought!</strong><br/>
                        £{$auction['winning_price']}
                      </td>
                    </tr>";
        }
        $output .= "</table></div>";
        echo $output;
      }
     ?>

   <a name="address"><h3>My Details</h3></a> <?php echo "<p>Average Feedback: <a href='feedback.php?for_user_id={$userId}'>"  . $average_stars_user['stars'] . " stars</a>.</p>";?>
   <p><b>My Address:</b><br>
   <?php echo $_SESSION['firstName'] . " " . $_SESSION['lastName'] . "<br>"; ?>
   <?php echo $_SESSION['street'] . "<br>"; ?>
   <?php echo $_SESSION['zip'] . " " . $_SESSION['city'] ."<br/>"; ?>
   <strong>Email</strong>: <?php echo " " . $_SESSION['email'] . "</p>"; ?>




    <?php
    $auction_set = filter_expired_auctions($auction_set_unfiltered);
    if ($auction_set) {
      $outputTableHeader = "<a name=\"bids\"><h3>My Recent Bids</h3></a>
  <table class=\"table table-striped\">
    <col width=\"20%\">
    <col width=\"60%\">
    <col width=\"20%\">
    <tr>
      <th>Item Name</th>
      <th>Description</th>
      <th>Status</th>
    </tr>";
      echo $outputTableHeader;
      foreach ($auction_set as $auction) {
        $imageName      = htmlentities($auction['imageName']);
        $title          = htmlentities($auction['title']);
        $description    = htmlentities($auction['description']);
        $winning_price  = htmlentities($auction['winning_price']);
        // echo $auction['winner_id'] . "<br/>";
        if($_SESSION['userId'] == $auction['winner_id'])
          $is_this_buyer = "<br><div id=\"this-you\">This is you!</div>";
        else
          $is_this_buyer = "
        <br><div id=\"this-not-you\">Your bid is not the winning bid!</div>";
        $link = "auction.php?auctionId=" .
            urlencode(htmlentities($auction['auctionId']));
        $output = "
      <tr>
        <td><a href=\"{$link}\"><h7>{$title}</h7>
        <img src=\"img/auctions/{$imageName}\"
        title=\"{$title}\">
        </a></td>
        <td>
          <strong>Description:</strong><br/>
          {$description}
        </td>
        <td>
          <Strong>Current price:</strong><br/>
          £{$winning_price}
          {$is_this_buyer}
        </td>
      </tr>";
        echo $output;
      }
    }
    ?>

    <?php
    $auction_set = retrieve_followed_by_user();
    if ($auction_set) {
      $outputTableHeader = "
      </table>
  <a name=\"following\"><h3>Following</h3></a>
  <table class=\"table table-striped\">
    <col width=\"15%\">
    <col width=\"50%\">
    <col width=\"20%\">
    <col width=\"15%\">
    <tr>
      <th>Item Name</th>
      <th>Description</th>
      <th>Current Price</th>
      <th>Unfollow</th>
    </tr>
      ";
      echo $outputTableHeader;
      foreach ($auction_set as $auction) {
        $imageName      = htmlentities($auction['imageName']);
        $title          = htmlentities($auction['title']);
        $description    = htmlentities($auction['description']);
        $winning_price  = htmlentities($auction['winning_price']);
        // echo $auction['winner_id'] . "<br/>";
        $is_this_buyer = "";
        if($_SESSION['userId'] == $auction['winner_id'])
        {$is_this_buyer = "<br><div id=\"this-you\">This is you!</div>";}
        if((time() - strtotime($auction['expirationDate'])) > 0) {
          $is_this_buyer .= "<br><div id=\"this-not-you\">
                            This auction is expired!</div>";
        }

        $link = "auction.php?auctionId=" .
            urlencode(htmlentities($auction['auctionId']));
        $link_delete_from_following =
            "buyer_account.php?auctionId=" .
            urlencode(htmlentities($auction['auctionId'])) .
            "#following";
        $output ="
      <tr>
        <td><a href=\"{$link}\"><h7>{$title}</h7>
        <img src=\"img/auctions/{$imageName}\"
        title=\"{$title}\"></a></td>
        <td>{$description}</td>
        <td>£{$winning_price} {$is_this_buyer}</td>
        <td class=\"trash\">
        <a href=\"$link_delete_from_following\">
        <img src=\"img/trash.svg\" class=\"trash-icon\"
        title=\"Unfollow\"></a></td>
      </tr>";
        echo $output;
      }
    }
    ?>

  </table>
    <?php
    $completedAuction = getCompletedAuctionDetailsForBuyer($userId);
    $output1 = "
      <a name=\"awarded-auctions\"><h3>Awarded Auctions</h3></a>
    <table class=\"table table-striped\">
      <col width=\"20%\">
      <col width=\"60%\">
      <col width=\"20%\">
      <tr>
        <th>Auction Item</th>
        <th>Corresponding Seller</th>
        <th>Final Price</th>
      </tr>
      ";
    if (mysqli_num_rows($completedAuction) !== 0) {
      echo $output1;
      while ($row = mysqli_fetch_assoc($completedAuction)) {
        $link = "auction.php?auctionId=" . urlencode($row['auction_id']);
        $output2 = "
       <tr>
        <td><a href=\"{$link}\"><div>{$row['title']}</div><img src=\"img/auctions/{$row['imageName']}\" title=\"{$row['title']}\"></a></td>
        <td>{$row['sellerName']}<br><strong>Address:</strong> {$row['sellerAddress']}<br><strong>Email:</strong> {$row['sellerEmail']}</td>
        <td>£{$row['finalAmount']}</td>
      </tr>";
        echo $output2;
      }
    } else {
      echo "";
    }
    ?>
    </table>
</div>
</div>

<?php
  include("../includes/layouts/footer.php");
?>