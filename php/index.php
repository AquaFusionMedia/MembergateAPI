<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    </head>
    <body>

        <?php

            if (isset($_POST['email']) && isset($_POST['userid'])) {
            
                if (!class_exists("members")) require_once(realpath(dirname(__FILE__) . "/mgapi/members.php"));
            
                $mgmembers = new members("*****************************","*****************************","https://www.mysite.com/api/request.cfm");

                function randomPassword() {
                    $pass = "";
                    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789!%&$#";
                    for ($i = 0; $i < 12; $i++) {
                        $n = rand(0, strlen($alphabet)-1);
                        $pass = $pass.$alphabet[$n];
                    }
                    return "1A".$pass."a";
                }
            
                $addMember = $mgmembers->addEditMember(first_name:$_POST['first_name'],last_name:$_POST['last_name'],email:$_POST['email'],account_type:$_POST['account_type'],userid:$_POST['userid'],password:randomPassword());

                ?>

                <div class="container p-4">
                    <div class="row">
                        <?php
                            if ($addMember['error']) {
                                foreach($addMember as $key => $value) {
                                    ?>
                                    <div class="col-md-3 text-right fw-bold"><?php echo $key; ?></div>
                                    <div class="col-md-9"><?php echo $value; ?></div>
                                    <?php
                                }
                            } else {
                                foreach($addMember['data'] as $key => $value) {
                                    ?>
                                    <div class="col-md-3 text-right fw-bold"><?php echo $key; ?></div>
                                    <div class="col-md-9"><?php 
                                        if (is_array($value)) {
                                            var_dump($value);
                                        } else {
                                            echo $value;
                                        } ?></div>
                                    <?php
                                }
                                ?>
                                <div class="col-md-3 text-right fw-bold">SSO Login</div>
                                <div class="col-md-9">
                                    <a target="_blank" href="https://www.mysite.com/members/main.cfm?sso=<?php echo $mgmembers->generateSSO($addMember['data']['sso_token'],$addMember['data']['memberuuid'],$addMember['data']['userid'],$addMember['data']['token']); ?>">Login</a>
                                </div>
                                <?php
                            }
                        ?>
                    </div>
                </div>

                <?php

            } else {

                ?>

                <form action="" method="post">
                    <div class="container p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name">
                                    <label for="first_name">First Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name">
                                    <label for="last_name">Last Name</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                                    <label for="email">Email</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="userid" class="form-control" id="userid" name="userid" placeholder="Username">
                                    <label for="userid">Username</label>
                                </div>
                            </div>
                            <div class="col-md-12 text-center">
                                <input type="hidden" name="account_type" value="38">
                                <button type="submit" class="btn btn-primary">Add User</button>
                            </div>
                        </div>
                    </div>                
                </form>

                <?php

            }

        ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>