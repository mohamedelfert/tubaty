<?php require_once 'inc/topHeader.php'; ?>
    <title><?php echo SITENAME; ?> - البريد الوارد </title>
<?php require_once 'inc/header.php'; ?>
<?php require_once 'inc/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">

            <?php require_once 'inc/sidbar.php'; ?>

            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header"><i class="glyphicon glyphicon-inbox"></i> البريد الوارد</h1>
                <div class="col-md-12">
                <?php
                if ($_SERVER['REQUEST_METHOD'] == "GET" and isset($_GET['delete'])){
                    $contact->deleteMessages($_GET['delete']);
                }
                ?>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">الاسم</th>
                                            <th class="text-center">البريد الالكتروني</th>
                                            <th class="text-center">الرساله</th>
                                            <th class="text-center">مشاهده</th>
                                            <th class="text-center">حذف</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i = 1;
                                    $per_page = 1;
                                    if (!isset($_GET['page'])){
                                        $page = 1;
                                    }else{
                                        $page = intval($_GET['page']);
                                    }
                                    $start = ($page - 1) * $per_page;
                                    $messages = $contact->getMessages("ORDER By id DESC LIMIT $start,$per_page");
                                    if (!empty($messages)):
                                        foreach ($messages as $value):
                                    ?>
                                        <tr>
                                            <td class="text-center"><?php echo $i++;?></td>
                                            <td class="text-center"><?php echo $value['username'];?></td>
                                            <td class="text-center"><?php echo $value['email'];?></td>
                                            <td class="text-center"><?php echo (mb_strlen($value['message'], "utf8") > 40 ? mb_substr($value['message'],0,40) . ' ...' : $value['message']);?></td>
                                            <td class="text-center"><a href="read.php?id=<?php echo $value['id'];?>" class="btn btn-sm btn-info">مشاهده</a></td>
                                            <td class="text-center"><a href="inbox.php?delete=<?php echo $value['id'];?>" class="btn btn-sm btn-danger">حذف</a></td>
                                        </tr>
                                    <?php
                                        endforeach;
                                    else:
                                    ?>
                                        <div class="alert alert-danger alert-dismissible text-center" role="alert">
                                            <strong>تنبيه !</strong> لا يوجد اي رسائل حاليا
                                        </div>
                                    <?php
                                    endif;
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <nav class="text-center">
                            <ul class="pagination">
                                <?php
                                $allMessages = $contact->getCountMessages();
                                $total_pages = ceil($allMessages / $per_page);
                                for ($i = 1;$i <= $total_pages;$i++){
                                    echo '<li '.($page == $i ? 'class="active"' : '').'><a href="inbox.php?page='.$i.'">'.$i.'</a></li>';
                                }
                                ?>
                            </ul>
                        </nav>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php require_once 'inc/footer.php'; ?>