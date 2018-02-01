## Installation

Copy UserProfile folder to `site/addons` folder

## Usage

Use like a standard Statamic Form:
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
                <label>Email</label>
                <input type="email" name="email" value="{{ old:username or username }}" class="form-control" />
            </div>
    
            <div class="form-group">
                <label>Class</label>
                <input type="text" name="class" value="{{ old:class or class }}" class="form-control" />
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
