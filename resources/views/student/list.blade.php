@extends('layout.app')
@section('content')
<div class=" mt-4">
    <div class="d-flex justify-content-between">
        <h2>All Student List</h2>
        <a href="manage_student.php" class="btn btn-primary">Add New Student</a>
    </div>
    <hr>
    <form class="mb-4 p-3 border rounded bg-light" method="GET" action="admin_students_list.php">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label text-sm">Search (Name, Email, Passport, Uni, Course)</label>
                <input type="text" class="form-control" id="search" name="search" value="">
            </div>
            <div class="col-md-3">
                <label for="agent" class="form-label">Filter by Agent</label>
                <select class="form-select" id="agent" name="agent">
                    <option value="">All Agents</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}">
                        {{ $agent->business_name ?? $agent->username }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="university" class="form-label">Filter by University</label>
                <select class="form-select" id="university" name="university">
                    <option value="">All Universities</option>
                    <option value="4">
                        DeVry University </option>
                    <option value="3">
                        GISMA Business School </option>
                    <option value="10">
                        National University of Singapore </option>
                    <option value="1">
                        PFH Private University of Applied Sciences </option>
                    <option value="6">
                        Post University </option>
                    <option value="5">
                        Simpson University </option>
                    <option value="2">
                        SRH University Heidelberg </option>
                    <option value="9">
                        University of Cape Town </option>
                    <option value="8">
                        University of Melbourne </option>
                    <option value="7">
                        University of Toronto </option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="course_title" class="form-label">Filter by Course Name</label>
                <select class="form-select" id="course_title" name="course_title">
                    <option value="">All Courses</option>
                    <option value="Bachelor of Applied Science in Computer Engineering" data-university-id="7">
                        Bachelor Of Applied Science In Computer Engineering </option>
                    <option value="Bachelor of Arts in Psychology" data-university-id="5">
                        Bachelor Of Arts In Psychology </option>
                    <option value="Bachelor of Business Administration" data-university-id="1">
                        Bachelor Of Business Administration </option>
                    <option value="Bachelor of Business Management" data-university-id="2">
                        Bachelor Of Business Management </option>
                    <option value="Bachelor of Commerce" data-university-id="8">
                        Bachelor Of Commerce </option>
                    <option value="Bachelor of Commerce in Actuarial Science" data-university-id="9">
                        Bachelor Of Commerce In Actuarial Science </option>
                    <option value="Bachelor of Computer Information Systems" data-university-id="4">
                        Bachelor Of Computer Information Systems </option>
                    <option value="Bachelor of Computer Science" data-university-id="2">
                        Bachelor Of Computer Science </option>
                    <option value="Bachelor of Computing (Computer Science)" data-university-id="10">
                        Bachelor Of Computing (Computer Science) </option>
                    <option value="Bachelor of Science" data-university-id="8">
                        Bachelor Of Science </option>
                    <option value="Bachelor of Science in Business Administration" data-university-id="6">
                        Bachelor Of Science In Business Administration </option>
                    <option value="Bachelor of Science in Nursing" data-university-id="5">
                        Bachelor Of Science In Nursing </option>
                    <option value="Global MBA" data-university-id="3">
                        Global MBA </option>
                    <option value="Juris Doctor (JD)" data-university-id="8">
                        Juris Doctor (JD) </option>
                    <option value="M.Sc. Business Analytics" data-university-id="10">
                        M.Sc. Business Analytics </option>
                    <option value="M.Sc. Chemical Engineering" data-university-id="9">
                        M.Sc. Chemical Engineering </option>
                    <option value="M.Sc. Composite Materials Engineering" data-university-id="1">
                        M.Sc. Composite Materials Engineering </option>
                    <option value="M.Sc. Data Science, AI &amp; Digital Business" data-university-id="3">
                        M.Sc. Data Science, AI &amp; Digital Business </option>
                    <option value="M.Sc. Project Management" data-university-id="3">
                        M.Sc. Project Management </option>
                    <option value="M.Sc. Psychology" data-university-id="2">
                        M.Sc. Psychology </option>
                    <option value="Master of Arts in Criminal Justice" data-university-id="5">
                        Master Of Arts In Criminal Justice </option>
                    <option value="Master of Business Administration (Full-time)" data-university-id="9">
                        Master Of Business Administration (Full-time) </option>
                    <option value="Master of Business Administration (MBA)" data-university-id="6">
                        Master Of Business Administration (MBA) </option>
                    <option value="Master of Engineering (Computer Engineering)" data-university-id="10">
                        Master Of Engineering (Computer Engineering) </option>
                    <option value="Master of Science in Computer Information Systems" data-university-id="6">
                        Master Of Science In Computer Information Systems </option>
                    <option value="Master of Science in Computer Science" data-university-id="7">
                        Master Of Science In Computer Science </option>
                    <option value="Master of Science in Information Security" data-university-id="4">
                        Master Of Science In Information Security </option>
                    <option value="MBA General Management" data-university-id="1">
                        MBA General Management </option>
                    <option value="MBA Project Management" data-university-id="4">
                        MBA Project Management </option>
                    <option value="Ph.D. in Biomedical Engineering" data-university-id="7">
                        Ph.D. In Biomedical Engineering </option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Filter by Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="student created">
                        Student Created </option>
                    <option value="Document received By idea">
                        Document Received By Idea </option>
                    <option value="Document Under review By Idea">
                        Document Under Review By Idea </option>
                    <option value="Documents submitted to University">
                        Documents Submitted To University </option>
                    <option value="Document Under review By University">
                        Document Under Review By University </option>
                    <option value="offer_letter_Pending">
                        Offer Letter Pending </option>
                    <option value="offer_letter_issued">
                        Offer Letter Issued </option>
                    <option value="offer_letter_declined">
                        Offer Letter Declined </option>
                    <option value="applied to another university">
                        Applied To Another University </option>
                    <option value="interview_scheduled">
                        Interview Scheduled </option>
                    <option value="Uni_deposit_paid">
                        Uni Deposit Paid </option>
                    <option value="visa_application_submitted">
                        Visa Application Submitted </option>
                    <option value="visa_interview_scheduled">
                        Visa Interview Scheduled </option>
                    <option value="visa_issued">
                        Visa Issued </option>
                    <option value="visa_rejected">
                        Visa Rejected </option>
                    <option value="enrolled">
                        Enrolled </option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="sort_by" class="form-label">Sort By</label>
                <select class="form-select" id="sort_by" name="sort_by">
                    <option value="id">
                        Id </option>
                    <option value="first_name">
                        First Name </option>
                    <option value="email">
                        Email </option>
                    <option value="university_name">
                        University Name </option>
                    <option value="course_title">
                        Course Title </option>
                    <option value="application_status">
                        Application Status </option>
                    <option value="agent_username">
                        Agent Username </option>
                    <option value="created_at" selected>
                        Created At </option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="sort_order" class="form-label">Sort Order</label>
                <select class="form-select" id="sort_order" name="sort_order">
                    <option value="ASC">Ascending</option>
                    <option value="DESC" selected>Descending</option>
                </select>
            </div>
            <div class="col-md-3 mt-3 d-flex justify-content-between">
                <button type="reset" class="btn btn-danger">Clear Filters</button>
                <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-hover border">
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>University</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Agent Name</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>23</td>
                    <td>Test 27</td>
                    <td>test27@test.com</td>
                    <td>National University of Singapore</td>
                    <td>Bachelor of Arts in Psychology</td>
                    <td><span class="badge bg-light text-dark">
                            Student Created</span></td>
                    <td>Client Outreach</td>
                    <td>2025-06-16 19:41</td>
                    <td>
                        <a href="manage_student.php?id=23" class="btn btn-sm btn-outline-primary me-1">View/Edit</a>
                        <a href="admin_delete_student.php?id=23" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student application?');">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>21</td>
                    <td>Agent prasad</td>
                    <td>agent@agent.com</td>
                    <td>Simpson University</td>
                    <td>Bachelor of Science in Nursing</td>
                    <td><span class="badge bg-light text-dark">
                            Student Created</span></td>
                    <td>Sales Connect</td>
                    <td>2025-06-15 09:43</td>
                    <td>
                        <a href="manage_student.php?id=21" class="btn btn-sm btn-outline-primary me-1">View/Edit</a>
                        <a href="admin_delete_student.php?id=21" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student application?');">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>17</td>
                    <td>Aisha Khan</td>
                    <td>aisha.khan@example.com</td>
                    <td>Post University</td>
                    <td>Bachelor of Science in Business Administration</td>
                    <td><span class="badge bg-light text-dark">
                            Student Created</span></td>
                    <td>Sales Connect</td>
                    <td>2025-06-15 00:23</td>
                    <td>
                        <a href="manage_student.php?id=17" class="btn btn-sm btn-outline-primary me-1">View/Edit</a>
                        <a href="admin_delete_student.php?id=17" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student application?');">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>18</td>
                    <td>Ben hash Carter</td>
                    <td>ben.carter@example.com</td>
                    <td>DeVry University</td>
                    <td>Bachelor of Computer Information Systems</td>
                    <td><span class="badge bg-light text-dark">
                            Student Created</span></td>
                    <td>Client Outreach</td>
                    <td>2025-06-15 00:23</td>
                    <td>
                        <a href="manage_student.php?id=18" class="btn btn-sm btn-outline-primary me-1">View/Edit</a>
                        <a href="admin_delete_student.php?id=18" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student application?');">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>19</td>
                    <td>Chen Li</td>
                    <td>chen.li@example.com</td>
                    <td>PFH Private University of Applied Sciences</td>
                    <td>Bachelor of Business Administration</td>
                    <td><span class="badge bg-light text-dark">
                            Student Created</span></td>
                    <td>N/A</td>
                    <td>2025-06-15 00:23</td>
                    <td>
                        <a href="manage_student.php?id=19" class="btn btn-sm btn-outline-primary me-1">View/Edit</a>
                        <a href="admin_delete_student.php?id=19" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student application?');">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>20</td>
                    <td>David Garcia</td>
                    <td>david.garcia@example.com</td>
                    <td>SRH University Heidelberg</td>
                    <td>Bachelor of Business Management</td>
                    <td><span class="badge bg-light text-dark">
                            Student Created</span></td>
                    <td>Sales Connect</td>
                    <td>2025-06-15 00:23</td>
                    <td>
                        <a href="manage_student.php?id=20" class="btn btn-sm btn-outline-primary me-1">View/Edit</a>
                        <a href="admin_delete_student.php?id=20" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student application?');">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>22</td>
                    <td>Ramesh Tamang</td>
                    <td>ramesh@tamang.com</td>
                    <td>GISMA Business School</td>
                    <td>Global MBA</td>
                    <td><span class="badge bg-light text-dark">
                            Student Created</span></td>
                    <td>Sales Connect</td>
                    <td>2025-06-15 00:23</td>
                    <td>
                        <a href="manage_student.php?id=22" class="btn btn-sm btn-outline-primary me-1">View/Edit</a>
                        <a href="admin_delete_student.php?id=22" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student application?');">Delete</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <nav aria-label="Student Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item disabled"><a class="page-link" href="?page=0">Previous</a></li>
            <li class="page-item active"><a class="page-link" href="?page=1">1</a></li>
            <li class="page-item disabled"><a class="page-link" href="?page=2">Next</a></li>
        </ul>
    </nav>
</div>
@endsection
