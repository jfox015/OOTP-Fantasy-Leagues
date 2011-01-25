        <ul>
            <?php if ($loggedIn) { ?>
            <li><a href="/user/collection">My Collection</a></li>
            <li><a href="/user">My Profile</a></li>
            <li><a href="/user/account">My Account Details</a></li>
            <li><a href="/user/preferences">My Preferences</a></li>
            <li><a href="/user/change_password">Change Password</a></li>
            <?php } else { ?>
            <li><a href="/search/users">Browse User Profiles</a></li>
            <?php } ?>
        </ul>