## Installation

Copy UserProfile folder to `site/addons` folder

## Usage

Please ensure your update form matches the type of login you have. For example, if your logins are set to `username` AND you want the user to be able to change their username, have a `username` form field.

If your login is set to `email`, then your email field should actually be a `username` field as that is what the user name is.

You can also have users change their password if you have two password fields, `password` & `password_confirmation`.

If you have `<input type="file"...`, users can upload files as well.

With `login_type: username`:
```
        {{ user_profile:edit_form redirect="/account" files="true" }}
            {{ if errors }}
                <div class="alert alert-danger">
                    {{ errors }}
                    {{ value }}<br>
                    {{ /errors }}
                </div>
            {{ /if }}
    
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="{{ old:username or username }}" class="form-control" />
            </div>
    
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old:email or email }}" class="form-control" />
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" />
            </div>

            <div class="form-group">
                <label>Password Confirmation</label>
                <input type="password" name="password_confirmation" class="form-control" />
            </div>

            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" value="{{ old:first_name or first_name }}" class="form-control" />
            </div>
    
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" value="{{ old:last_name or last_name }}" class="form-control" />
            </div>
    
            <div class="form-group">
                <label>Bio</label>
                <input type="text" name="content" value="{{ old:content or content }}" class="form-control" />
            </div>
    
            <div class="form-group">
                <label>Profile image</label>
                <input type="file" name="photo" />
            </div>
    
            <button class="btn btn-primary">Update</button>
        {{ /user_profile:edit_form }}
```

With `login_type: email`:

```
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="username" value="{{ old:username or username }}" class="form-control" />
            </div>


```