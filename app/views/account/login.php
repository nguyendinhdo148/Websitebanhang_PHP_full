<?php include 'app/views/shares/header.php'; ?>

<section class="vh-100 gradient-custom">
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card bg-dark text-white" style="border-radius: 1rem;">
                    <div class="card-body p-5 text-center">
                        <?php if (isset($_SESSION['login_error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['login_error'] ?>
                                <?php unset($_SESSION['login_error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="login-form" action="/webbanhang/account/checkLogin" method="POST">
                            <div class="mb-md-5 mt-md-4 pb-5">
                                <h2 class="fw-bold mb-2 text-uppercase">Login</h2>
                                <p class="text-white-50 mb-5">Please enter your login and password!</p>
                                
                                <div class="form-outline form-white mb-4">
                                    <input type="text" name="username" class="form-control form-control-lg" required />
                                    <label class="form-label" for="typeEmailX">UserName</label>
                                </div>
                                
                                <div class="form-outline form-white mb-4">
                                    <input type="password" name="password" class="form-control form-control-lg" required />
                                    <label class="form-label" for="typePasswordX">Password</label>
                                </div>
                                
                                <p class="small mb-5 pb-lg-2">
                                    <a class="text-white-50" href="#!">Forgot password?</a>
                                </p>
                                
                                <button class="btn btn-outline-light btn-lg px-5" type="submit">Login</button>
                                
                                <div class="d-flex justify-content-center text-center mt-4 pt-1">
                                    <a href="#!" class="text-white"><i class="fab fa-facebook-f fa-lg"></i></a>
                                    <a href="#!" class="text-white mx-4 px-2"><i class="fab fa-twitter fa-lg"></i></a>
                                    <a href="#!" class="text-white"><i class="fab fa-google fa-lg"></i></a>
                                </div>
                            </div>
                            
                            <div>
                                <p class="mb-0">Don't have an account? 
                                    <a href="/webbanhang/account/register" class="text-white-50 fw-bold">Sign Up</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'app/views/shares/footer.php'; ?>

<script>
    // Xử lý form đăng nhập với cả 2 phương thức (session và JWT)
    document.getElementById('login-form').addEventListener('submit', function(event) {
        event.preventDefault();

        // Nếu chỉ muốn dùng session truyền thống (không phải API), hãy bỏ đoạn fetch này:
        // window.location = this.action; this.submit(); return false;

        // Nếu muốn dùng fetch, cần backend trả về JSON đúng chuẩn khi là API.
        // Nếu backend chỉ trả về HTML/redirect, hãy dùng submit truyền thống:
        this.submit();
        return false;

        // Nếu muốn dùng fetch, đảm bảo backend có endpoint checkLoginAPI trả về JSON.
        /*
        const formData = new FormData(this);
        const jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        const isApiRequest = document.getElementById('api-login-check')?.checked;
        const endpoint = isApiRequest ? '/webbanhang/account/checkLoginAPI' : '/webbanhang/account/checkLogin';

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(jsonData)
        })
        .then(response => {
            if (response.redirected && !isApiRequest) {
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data?.token) {
                localStorage.setItem('jwtToken', data.token);
                window.location.href = '/webbanhang/Product';
            } else if (data?.message) {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đăng nhập thất bại. Vui lòng thử lại.');
        });
        */
    });
    
    // Kiểm tra nếu có token JWT trong localStorage thì chuyển hướng
    if (localStorage.getItem('jwtToken')) {
        window.location.href = '/webbanhang/Product';
    }
</script>