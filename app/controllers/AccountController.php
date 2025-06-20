<?php
require_once('app/config/database.php');
require_once('app/models/AccountModel.php');
require_once('app/utils/JWTHandler.php');

class AccountController
{
    private $accountModel;
    private $db;
    private $jwtHandler;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
        $this->jwtHandler = new JWTHandler();
    }

    function register()
    {
        include_once 'app/views/account/register.php';
    }
    
    public function login()
    {
        include_once 'app/views/account/login.php';
    }

    function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $fullName = $_POST['fullname'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmpassword'] ?? '';
            $email = $_POST['email'] ?? '';
            $phoneNumber = $_POST['phoneNumber'] ?? '';
            $avatar = null;

            $errors = [];
            if (empty($username)) {
                $errors['username'] = "Vui lòng nhập userName!";
            }
            if (empty($fullName)) {
                $errors['fullname'] = "Vui lòng nhập fullName!";
            }
            if (empty($password)) {
                $errors['password'] = "Vui lòng nhập password!";
            }
            if ($password != $confirmPassword) {
                $errors['confirmPass'] = "Mật khẩu và xác nhận chưa khớp";
            }
            
            // Kiểm tra username đã được đăng ký chưa?
            $account = $this->accountModel->getAccountByUsername($username);

            if ($account) {
                $errors['account'] = "Tài khoản này đã có người đăng ký!";
            }

            // Validate email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Email không hợp lệ!";
            }

            // Validate phone number
            if (empty($phoneNumber) || !preg_match("/^[0-9]{10}$/", $phoneNumber)) {
                $errors['phoneNumber'] = "Số điện thoại không hợp lệ!";
            }

            // Handle avatar upload
            $avatar = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'public/uploads/avatars/';
                
                // Create directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileExtension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (!in_array($fileExtension, $allowedTypes)) {
                    $errors['avatar'] = "Chỉ chấp nhận file ảnh dạng JPG, JPEG, PNG & GIF.";
                } elseif ($_FILES['avatar']['size'] > 5000000) { // 5MB limit
                    $errors['avatar'] = "File ảnh không được vượt quá 5MB.";
                } else {
                    $newFileName = uniqid() . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                        $avatar = $uploadPath; // Store the path relative to the project root
                    } else {
                        $errors['avatar'] = "Không thể tải file lên. Vui lòng thử lại.";
                    }
                }
            }

            if (count($errors) > 0) {
                include_once 'app/views/account/register.php';
            } else {
                try {
                    $password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $result = $this->accountModel->save($username, $fullName, $password, 'user', $email, $phoneNumber, $avatar);
                    
                    if ($result) {
                        header('Location: /webbanhang/account/login');
                        exit();
                    }
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Email đã được sử dụng') !== false) {
                        $errors['email'] = "Email này đã được sử dụng";
                    } else {
                        $errors['general'] = "Có lỗi xảy ra, vui lòng thử lại sau";
                    }
                    include_once 'app/views/account/register.php';
                }
            }
        }
    }

    private function uploadAvatar($file)
    {
        $target_dir = "uploads/avatars/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is actual image
        if (!getimagesize($file["tmp_name"])) {
            return "Error: File không phải là hình ảnh.";
        }

        // Check file size (5MB max)
        if ($file["size"] > 5000000) {
            return "Error: File quá lớn.";
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            return "Error: Chỉ chấp nhận file JPG, JPEG, PNG & GIF.";
        }

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_file;
        }

        return "Error: Có lỗi khi tải file lên.";
    }
    
    function logout()
{
    // Bước 1: Nếu có token trong cookie => xóa cookie
    if (isset($_COOKIE['auth_token'])) {
        setcookie('auth_token', '', time() - 10, '/', '', isset($_SERVER['HTTPS']), true);
    }

    // Bước 2: Kiểm tra nếu có Authorization Bearer token => API client đang dùng header
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    $isApiRequest = false;

    if (strpos($authHeader, 'Bearer ') === 0) {
        $isApiRequest = true;
    }

    // Bước 3: Hủy session nếu có (web login)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    session_unset();
    session_destroy();

    // Bước 4: Nếu là gọi từ fetch/Postman (API) thì trả về JSON
    if (
        $isApiRequest ||
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    ) {
        header('Content-Type: application/json');
        echo json_encode([
            'message' => 'Đăng xuất thành công. Vui lòng xóa token khỏi client nếu đang dùng JWT.'
        ]);
        exit;
    }

    // Bước 5: Nếu là request từ trình duyệt (web) thì chuyển hướng
    header('Location: /webbanhang/account/login');
    exit;
}

    public function checkLogin()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Check if it's an API request (JSON content type)
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $data = json_decode(file_get_contents("php://input"), true);
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';
            $isApiRequest = true;
        } else {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $isApiRequest = false;
        }

        $account = $this->accountModel->getAccountByUserName($username);
        
        if ($account && password_verify($password, $account->password)) {
            // Thêm thời gian hết hạn token vào payload
            $payload = [
                'iat' => time(),                       // thời điểm phát hành token
                'exp' => time() + 10,                // thời điểm hết hạn: sau 1 giờ
                'data' => [
                    'id' => $account->id,
                    'username' => $account->username,
                    'fullname' => $account->fullname,
                    'role' => $account->role
                ]
            ];

            $token = $this->jwtHandler->encode($payload);

            if ($isApiRequest) {
                header('Content-Type: application/json');
                echo json_encode([
                    'token' => $token,
                    'user' => $payload['data']
                ]);
                exit;
            } else {
                // For web requests, store token in cookie and user info in session
                setcookie('auth_token', $token, [
                    'expires' => time() + 10, // 1 hour
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);

                // Start session if not already started
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $_SESSION['user'] = $payload['data'];
                header('Location: /webbanhang/product');
                exit;
            }
        } else {
            if ($isApiRequest) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['message' => 'Thông tin đăng nhập không hợp lệ']);
                exit;
            } else {
                // For web requests, set error message and redirect back to login
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['login_error'] = "Tài khoản hoặc mật khẩu không chính xác";
                header('Location: /webbanhang/account/login');
                exit;
            }
        }
    }
}

    
    public function list() {
        // Check if user is admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error_message'] = 'Bạn không có quyền truy cập trang này';
            header('Location: /webbanhang/Product');
            exit();
        }

        $users = $this->accountModel->getAllUsers();
        include 'app/views/account/list.php';
    }

    public function editUser($id) 
    {
        // Check admin permission
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error_message'] = 'Bạn không có quyền truy cập trang này';
            header('Location: /webbanhang/Product');
            exit();
        }

        $user = $this->accountModel->getUserById($id);
        if ($user) {
            include 'app/views/account/editUser.php';
        } else {
            $_SESSION['error_message'] = 'Không tìm thấy người dùng';
            header('Location: /webbanhang/account/list');
            exit();
        }
    }

    public function updateUser() 
    {
        // Check admin permission
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error_message'] = 'Bạn không có quyền thực hiện thao tác này';
            header('Location: /webbanhang/Product');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $username = $_POST['username'];
            $fullname = $_POST['fullname'];
            $role = $_POST['role'];

            if ($this->accountModel->updateUser($id, $username, $fullname, $role)) {
                $_SESSION['success_message'] = 'Cập nhật người dùng thành công';
            } else {
                $_SESSION['error_message'] = 'Cập nhật người dùng thất bại';
            }
            header('Location: /webbanhang/account/list');
            exit();
        }
    }

    public function view($id = null) 
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error_message'] = 'Vui lòng đăng nhập để xem thông tin';
            header('Location: /webbanhang/account/login');
            exit();
        }

        // If no ID provided, show current user's profile
        if ($id === null) {
            $id = $_SESSION['user']['id'];
        }

        // Only allow users to view their own profile unless they're admin
        if ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['id'] != $id) {
            $_SESSION['error_message'] = 'Bạn không có quyền xem thông tin này';
            header('Location: /webbanhang/Product');
            exit();
        }

        $user = $this->accountModel->getUserById($id);
        if ($user) {
            include 'app/views/account/detail.php';
        } else {
            $_SESSION['error_message'] = 'Không tìm thấy thông tin người dùng';
            header('Location: /webbanhang/Product');
            exit();
        }
    }

    public function delete($id) {
        // Check admin permission
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error_message'] = 'Bạn không có quyền thực hiện thao tác này';
            header('Location: /webbanhang/Product');
            exit();
        }

        // Get user info before delete
        $user = $this->accountModel->getUserById($id);
        if (!$user) {
            $_SESSION['error_message'] = 'Không tìm thấy người dùng';
            header('Location: /webbanhang/account/list');
            exit();
        }

        // Prevent deleting admin account
        if ($user->role === 'admin') {
            $_SESSION['error_message'] = 'Không thể xóa tài khoản admin';
            header('Location: /webbanhang/account/list');
            exit();
        }

        try {
            if ($this->accountModel->deleteUser($id)) {
                $_SESSION['success_message'] = 'Xóa người dùng thành công';
            } else {
                $_SESSION['error_message'] = 'Xóa người dùng thất bại';
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Không thể xóa người dùng này. Vui lòng thử lại sau.';
        }
        
        header('Location: /webbanhang/account/list');
        exit();
    }

    public function profile()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /webbanhang/account/login');
            exit();
        }
        
        $user = $this->accountModel->getUserById($_SESSION['user']['id']);
        include 'app/views/account/profile.php';
    }

    public function editProfile()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /webbanhang/account/login');
            exit();
        }
        
        $user = $this->accountModel->getUserById($_SESSION['user']['id']);
        include 'app/views/account/editProfile.php';
    }

    public function updateProfile()
    {
        if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /webbanhang/account/login');
            exit();
        }

        $errors = [];
        $userId = $_SESSION['user']['id'];
        $email = $_POST['email'] ?? '';
        $phoneNumber = $_POST['phoneNumber'] ?? '';
        $fullName = $_POST['fullname'] ?? '';

        // Validate inputs
        if (empty($fullName)) {
            $errors['fullname'] = "Vui lòng nhập họ tên!";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Email không hợp lệ!";
        }

        if (empty($phoneNumber) || !preg_match("/^[0-9]{10}$/", $phoneNumber)) {
            $errors['phoneNumber'] = "Số điện thoại không hợp lệ!";
        }

        // Handle avatar upload
        $avatarPath = $_POST['existing_avatar'] ?? null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadAvatar($_FILES['avatar']);
            if (strpos($uploadResult, 'Error:') === 0) {
                $errors['avatar'] = substr($uploadResult, 7);
            } else {
                $avatarPath = $uploadResult;
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /webbanhang/account/editProfile');
            exit();
        }

        if ($this->accountModel->updateProfile($userId, $fullName, $email, $phoneNumber, $avatarPath)) {
            $_SESSION['success_message'] = 'Cập nhật thông tin thành công';
            // Update session information
            $_SESSION['user']['fullname'] = $fullName;
            header('Location: /webbanhang/account/profile');
        } else {
            $_SESSION['error_message'] = 'Có lỗi xảy ra khi cập nhật thông tin';
            header('Location: /webbanhang/account/editProfile');
        }
        exit();
    }
}