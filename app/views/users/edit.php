<h2>Edit Profile</h2>

<form method="POST" action="<?php echo BASE_URL; ?>users/update">
    <div class="mb-3">
        <label>First Name</label>
        <input type="text" name="first_name" class="form-control"
               value="<?php echo htmlspecialchars($data['user']['first_name']); ?>">
    </div>

    <div class="mb-3">
        <label>Last Name</label>
        <input type="text" name="last_name" class="form-control"
               value="<?php echo htmlspecialchars($data['user']['last_name']); ?>">
    </div>

    <button class="btn btn-primary">Save Changes</button>
</form>
