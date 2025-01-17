<?php

function checkUserPermissions($user_role_id, $required_role_id) {
    // Hierarchy: Admin (1) > Manager (2) > Employee (3)
    return $user_role_id <= $required_role_id;
}

function isUserResponsible($user_id, $item_owner_id) {
    // Check if the user is responsible for the item
    return $user_id === $item_owner_id;
}
?>
