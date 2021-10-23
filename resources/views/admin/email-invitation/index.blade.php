<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Email invitaion</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css"/>

</head>
<body>
<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container">
        <ul>
            <li class="nav-item dropdown">
                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false" v-pre style="float: right;">
                    {{ Auth::user()->name }}
                </a>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                        {{ __('Logout') }}
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>

<div class="card mt-5 ml-5 mr-5 mb-5">
    <div class="card-header">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2 class="card-title">Email Invitation</h2>
            </div>
            <div class="col-sm-6 col-12">
                <a href="#addUserInvitation" class="btn btn-success" style="float: right;" data-toggle="modal">Add
                    users</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserInvitation" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add user invitation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>
                        Name
                    </label><br>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Type name..." required>
                    <label>
                        Email
                    </label><br>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Type email..."
                           required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="send_invitation">Send invite</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $sendInvite = '{{URL::route('send.invite')}}';
    $token = '{{csrf_token()}}';
    $(document).ready(function () {

    });
    $('body').on('click', '#send_invitation', function () {
        $name = $('#name').val();
        $email = $('#email').val();
        $formData = {
            '_token': $token,
            email: $email,
            name: $name,
        };
        $.ajax({
            url: $sendInvite,
            type: 'POST',
            data: $formData,
            success: function (response) {
                if (response.success === true) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            }
        })
    });
</script>
</body>
</html>
