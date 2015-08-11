<!DOCTYPE html>
<html>
<head>
<title>BitPay Installation Script</title>
</head>
<?php
    $step = (isset($_GET['step']) && $_GET['step'] != '') ? $_GET['step'] : '';
    switch($step){
        case '1':
        step_1();
        break;
        case '2':
        step_2();
        break;
        case '3':
        step_3();
        break;
        default:
        step_1();
    }
?>
<body>
    <?php
    //Step 1: Beginning installation message, no functional purpose
    function step_1() {
    ?>
        <p>This install script will check the directories for the version of Membership that you are using and then install the bitpay plugin files in their appropriate directories. Press continue to proceed with installation.</p>
        <form action="bitpayinstall.php?step=2" method="post">
            <input type="submit" value="Continue" />
        </form>
    <?php
    }
    //Step 2: Checks if the required folders exist and have the proper permissions.
    function step_2() {
        //search for wordpress root folder
        $directories = new RecursiveIteratorIterator(
            new ParentIterator(new RecursiveDirectoryIterator(".")),
            RecursiveIteratorIterator::SELF_FIRST);
        $wp_content_directory = '';
        foreach ($directories as $directory) {
            if (basename(realpath($directory)) == 'wp-content') {
                $wp_content_directory = realpath($directory);
                break;
            }
        }
        //check if wp-content exists
        if ($wp_content_directory == '') {
            echo "wp-content not found. Please make sure that bitpayinstall.php is in the root of your word press directory.";
            die();
        }
        //check plugins folder
        $plugins_temp = $wp_content_directory . '/plugins';
        if (!is_dir($plugins_temp)) {
            echo ".../wp-content/plugins folder not found. Please check to see if membership is installed.";
            die();
        }
        if (!is_writeable($plugins_temp)) {
            echo $plugins_temp . " is not writeable. Please check your permissions for this folder.";
            die();
        }
        $plugins_directory = $plugins_temp;
        //check gateway folder
        if (is_dir($wp_content_directory . '/plugins/membership/membershipincludes/gateways')) {
            $gateway_temp = $wp_content_directory . '/plugins/membership/membershipincludes/gateways';
        } else if (is_dir($wp_content_directory . '/plugins/membership/app_old/membershipincludes/gateways')){
            $gateway_temp = $wp_content_directory . '/plugins/membership/app_old/membershipincludes/gateways';
        } else {
            echo ".../membershipincludes/gateways folder not found. Please check to see if membership is installed.";
            die();
        }
        if (!is_writeable($gateway_temp)) {
            echo $gateway_temp . " is not writeable. Please check your permissions for this folder.";
            die();
        }
        $gateway_directory = $gateway_temp;
        //check bitpay-files folder
        if (!is_dir(getcwd() . "/bitpay-files")) {
            echo "bitpay-files missing, please make sure that the bitpay-files are in the same directory as bitpayinstall.php";
            die();
        }
        if (!is_writeable(getcwd() . "/bitpay-files")) {
            echo "bitpay-files are not writeable, please make sure that the folder has 0777 permissions.";
            die();
        }
        $bitpay_files_directory = getcwd() . "/bitpay-files";
        if ($pre_error == '') {
            //no errors, show continue button
        ?>
            <p>All files accessible and no errors. Proceed with installation in <?php echo $wp_content_directory;?></p>
            <form action="bitpayinstall.php?step=3" method="post">
                <input type="hidden" name="bitpay_files_directory" id="bitpay_files_directory" value="<?php echo $bitpay_files_directory;?>" />
                <input type="hidden" name="plugins_directory" id="plugins_directory" value="<?php echo $plugins_directory;?>" />
                <input type="hidden" name="gateway_directory" id="plugins_directory" value="<?php echo $gateway_directory;?>" />
                <input type="submit" name="continue" value="Continue" />
            </form>
        <?php
        } else {
            //hide continue button and display errors
            echo $pre_error;
        }
    }
    //Step 3: Finally moves the files over to their respective locations based on the submission form from step 2. bitpay-files will be deleted afterwards. The user will have to delete bitpayinstall.php afterwards.
    function step_3() {
        //if it exists, proceed to move files.
        $bitpay_files_directory = $_POST['bitpay_files_directory'];
        $plugins_directory = $_POST['plugins_directory'];
        $gateway_directory = $_POST['gateway_directory'];
        if ($bitpay_files_directory == '' && $plugins_directory == '' && $gateway_directory == '') {
        ?>
            <p>If you need to reinstall, please restart the installation. If you already installed, please delete bitpayinstall.php</p>
            <form action="bitpayinstall.php?step=1" method="post">
                <input type="submit" value="Restart" />
            </form>
        <?php
            die();
        }
        //move gateway.bitpay.php
        rename($bitpay_files_directory . "/gateway.bitpay.php", $gateway_directory . "/gateway.bitpay.php");
        //move bp_lib.php, bp_options.php, form.php
        $helper_destination = $plugins_directory . "/bitpay-form-helper";
        mkdir($helper_destination);
        $files = scandir($bitpay_files_directory);
        foreach ($files as $file) {
            if ($file != "gateway.bitpay.php" && $file != "." && $file != "..") {
                copy($bitpay_files_directory . "/" . $file, $helper_destination . "/" . $file);
                $delete[] = $bitpay_files_directory . "/" . $file;
            }
        }
        foreach($delete as $file) {
            unlink($file);
        }
        //check if the bitpay_files is empty and remove it
        if (count(glob($bitpay_files_directory . "/*")) === 0 && $bitpay_files_directory != '') {
            rmdir($bitpay_files_directory);
        } else {
            $pre_error = "Something went wrong with moving files; the bitpay-files folder is not empty.";
        }
        if ($pre_error == '') {
            //no errors, show continue button
        ?>
            <p>Installation was successful! Please manually delete bitpayinstall.php and any other bitpay installation files before continuing.</p>
            <form action="wp-admin" method="post">
                <input type="submit" value="Continue" />
            </form>
        <?php
        } else {
            //hide continue button and display errors
            echo $pre_error;
        }
    }
    ?>
</body>
</html>
