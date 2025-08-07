@extends('layout.admin')

@section('admin-content')
<div class="mt-4">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white shadow">
                <div class="card-body">
                    <h5 class="card-title">New Applications</h5>
                    <p class="card-text fs-2">0</p>
                    <a href="admin_students_list.php?status_filter=Pending" class="btn btn-sm btn-light">View All</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-dark shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Agents</h5>
                    <p class="card-text fs-2">3</p>
                    <a href="admin_students_list.php?status_filter=Documents Submitted" class="btn btn-sm btn-dark">View All</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-secondary text-white shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Students</h5>
                    <p class="card-text fs-2">7</p>
                    <a href="admin_students_list.php" class="btn btn-sm btn-light">View All</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Universities</h5>
                    <p class="card-text fs-2">10</p>
                    <a href="universities_list.php" class="btn btn-sm btn-light">View All</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Additional dashboard content can go here -->
</div>
<section class="content">
    <div class="left-content">
        <div class="activities">
            <h4 class="text-center p-2">This is the ADMIN PANEL FOR IDEA CONSULTANCY SERVICES</h4>
            <div class="activity-container">
                <div class="image-container img-one">
                    <img src="https://images.pexels.com/photos/356844/pexels-photo-356844.jpeg?auto=compress&cs=tinysrgb&w=600" alt="tennis" />
                    <div class="overlay">
                        <h3>USA</h3>
                    </div>
                </div>

                <div class="image-container img-two">
                    <img src="https://images.pexels.com/photos/51363/london-tower-bridge-bridge-monument-51363.jpeg?auto=compress&cs=tinysrgb&w=600" alt="hiking" />
                    <div class="overlay">
                        <h3>UK</h3>
                    </div>
                </div>

                <div class="image-container img-three">
                    <img src="https://images.pexels.com/photos/109629/pexels-photo-109629.jpeg?auto=compress&cs=tinysrgb&w=600" alt="hiking" />
                    <div class="overlay">
                        <h3>Germany</h3>
                    </div>
                </div>

                <div class="image-container img-four">
                    <img src="https://images.pexels.com/photos/548077/pexels-photo-548077.jpeg?auto=compress&cs=tinysrgb&w=600" alt="cycling" />
                    <div class="overlay">
                        <h3>France</h3>
                    </div>
                </div>

                <div class="image-container img-five">
                    <img src="https://images.pexels.com/photos/3254729/pexels-photo-3254729.jpeg?auto=compress&cs=tinysrgb&w=600" alt="yoga" />
                    <div class="overlay">
                        <h3>Spain</h3>
                    </div>
                </div>

                <div class="image-container img-six">
                    <img src="https://images.pexels.com/photos/325193/pexels-photo-325193.jpeg?auto=compress&cs=tinysrgb&w=600" alt="swimming" />
                    <div class="overlay">
                        <h3>Dubai</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="left-bottom">
            <div class="weekly-schedule">
                <h1>Activities Coming Soon</h1>
                <div class="calendar">
                    <div class="day-and-activity activity-one">
                        <div class="day">
                            <h1>13</h1>
                            <p>mon</p>
                        </div>
                        <div class="activity">
                            <h2>Agents Webinar</h2>
                            <div class="participants">
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/c61daa1c-5881-43f8-a50f-62be3d235daf" style="--i: 1" alt="" />
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/90affa88-8da0-40c8-abe7-f77ea355a9de" style="--i: 2" alt="" />
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/07d4fa6f-6559-4874-b912-3968fdfe4e5e" style="--i: 3" alt="" />
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/e082b965-bb88-4192-bce6-0eb8b0bf8e68" style="--i: 4" alt="" />
                            </div>
                        </div>
                        <button class="btn">Join</button>
                    </div>

                    <div class="day-and-activity activity-two">
                        <div class="day">
                            <h1>15</h1>
                            <p>wed</p>
                        </div>
                        <div class="activity">
                            <h2>University Training</h2>
                            <div class="participants">
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/c61daa1c-5881-43f8-a50f-62be3d235daf" style="--i: 1" alt="" />
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/32037044-f076-433a-8a6e-9b80842f29c9" style="--i: 2" alt="" />
                            </div>
                        </div>
                        <button class="btn">Join</button>
                    </div>

                    <div class="day-and-activity activity-three">
                        <div class="day">
                            <h1>17</h1>
                            <p>fri</p>
                        </div>
                        <div class="activity">
                            <h2>Visa Preperation Classes</h2>
                            <div class="participants">
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/32037044-f076-433a-8a6e-9b80842f29c9" style="--i: 1" alt="" />
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/e082b965-bb88-4192-bce6-0eb8b0bf8e68" style="--i: 2" alt="" />
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/c61daa1c-5881-43f8-a50f-62be3d235daf" style="--i: 3" alt="" />
                            </div>
                        </div>
                        <button class="btn">Join</button>
                    </div>

                    <div class="day-and-activity activity-four">
                        <div class="day">
                            <h1>18</h1>
                            <p>sat</p>
                        </div>
                        <div class="activity">
                            <h2>Agent Portal Training</h2>
                            <div class="participants">
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/07d4fa6f-6559-4874-b912-3968fdfe4e5e" style="--i: 1" alt="" />
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/32037044-f076-433a-8a6e-9b80842f29c9" style="--i: 2" alt="" />
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/07d4fa6f-6559-4874-b912-3968fdfe4e5e" alt="" />
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/c61daa1c-5881-43f8-a50f-62be3d235daf" style="--i: 4" alt="" />
                                <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/90affa88-8da0-40c8-abe7-f77ea355a9de" style="--i: 5" alt="" />
                            </div>
                        </div>
                        <button class="btn">Join</button>
                    </div>
                </div>
            </div>

            <div class="personal-bests">
                <h1>Upcoming Intakes</h1>
                <div class="personal-bests-container">
                    <div class="best-item box-one">
                        <p>August Intake: German, Usa, France, Spain, </p>
                        <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/242bbd8c-aaf8-4aee-a3e4-e0df62d1ab27" alt="" />
                    </div>
                    <div class="best-item box-two">
                        <p>Jan Intake for: German, USA, France</p>
                        <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/a3b3cb3a-5127-498b-91cc-a1d39499164a" alt="" />
                    </div>
                    <div class="best-item box-three">
                        <p>July Intake: Thailand, Spain, German</p>
                        <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/e0ee8ffb-faa8-462a-b44d-0a18c1d9604c" alt="" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="right-content">
        <div class="user-info">
            <div class="icon-container">
                <i class="fa fa-bell nav-icon"></i>
                <i class="fa fa-message nav-icon"></i>
            </div>

            <h4>Admin Dashboard!</h4>
            <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/40b7cce2-c289-4954-9be0-938479832a9c" alt="user" />
        </div>
        <div class="active-calories">
            <h1 style="align-self: flex-start">Application status</h1>
            <div class="active-calories-container">
                <div class="box" style="--i: 50%">
                    <div class="circle">
                        <h2>105</h2>
                    </div>
                </div>
                <div class="calories-content">
                    <p><span>Noc Pending:</span> 30</p>
                    <p><span>University Applied:</span> 50</p>
                    <p><span>Waiting for Interview:</span> 25</p>
                </div>
            </div>
        </div>

        <div class="mobile-personal-bests">
            <h1>Upcoming Intakes</h1>
            <div class="personal-bests-container">
                <div class="best-item box-one">
                    <p>August Intake: German, Usa, France, Spain, </p>
                    <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/242bbd8c-aaf8-4aee-a3e4-e0df62d1ab27" alt="" />
                </div>
                <div class="best-item box-two">
                    <p>Jan Intake for: German, USA, France</p>
                    <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/a3b3cb3a-5127-498b-91cc-a1d39499164a" alt="" />
                </div>
                <div class="best-item box-three">
                    <p>July Intake: Thailand, Spain, German</p>
                    <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/e0ee8ffb-faa8-462a-b44d-0a18c1d9604c" alt="" />
                </div>
            </div>
        </div>

        <div class="friends-activity">
            <h1>Recent Activity</h1>
            <div class="card-container">
                <div class="card">
                    <div class="card-user-info">
                        <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/9290037d-a5b2-4f50-aea3-9f3f2b53b441" alt="" />
                        <h2>Student Ramesh </h2>
                    </div>
                    <p>All Documents uploaded in portal</p>
                </div>

                <div class="card card-two">
                    <div class="card-user-info">
                        <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/42616ef2-ba96-49c7-80ea-c3cf1e2ecc89" alt="" />
                        <h2>Student Suresh </h2>
                    </div>
                    <p>Student is preparing Document in ward</p>
                </div>
                <div class="card card-two">
                    <div class="card-user-info">
                        <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/42616ef2-ba96-49c7-80ea-c3cf1e2ecc89" alt="" />
                        <h2>Student Dinesh </h2>
                    </div>
                    <p>All Document send for NOC approval</p>
                </div>
                <div class="card card-two">
                    <div class="card-user-info">
                        <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/42616ef2-ba96-49c7-80ea-c3cf1e2ecc89" alt="" />
                        <h2>Student Gita </h2>
                    </div>
                    <p>Waiting for documents from student</p>
                </div>
                <div class="card card-two">
                    <div class="card-user-info">
                        <img src="https://github.com/ecemgo/mini-samples-great-tricks/assets/13468728/42616ef2-ba96-49c7-80ea-c3cf1e2ecc89" alt="" />
                        <h2>Student Sita </h2>
                    </div>
                    <p>All Document submitted and waiting for visa interview</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
