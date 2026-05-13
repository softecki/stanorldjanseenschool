import React from 'react';
import { Link, Navigate, Route, Routes, useParams } from 'react-router-dom';
import { AcademicFormPage, AcademicListPage, AcademicViewPage, CLASSES_LIST_COLUMNS } from './academic/AcademicPages';
import { BackendDashboardPage, BackendDashboardPdfPage, BackendDashboardTablePage, BackendMasterPage, BackendMenuAutocompletePage } from './backend/BackendPages';
import { AttendanceIndexPage, AttendanceNotificationPage, AttendanceReportPage } from './attendance/AttendancePages';
import {
    AccountHeadFormPage,
    AccountHeadViewPage,
    AccountHeadsPage,
    AccountingDashboardPage,
    AccountsDataReportPage,
    AccountsHomePage,
    CashFormPage,
    CashPage,
    ChartOfAccountsFormPage,
    ChartOfAccountsPage,
    ChartOfAccountsViewPage,
    ExpenseFormPage,
    ExpensePage,
    IncomeFormPage,
    IncomePage,
    ItemCreatePage,
    ItemPage,
    PaymentMethodFormPage,
    PaymentMethodViewPage,
    PaymentMethodsPage,
    ProductCreatePage,
    ProductPage,
    ProductSellPage,
} from './accounts/AccountsAppPages';
import { DepositFormPage, DepositsPage, InvoiceFormPage, InvoicesPage, PaymentFormPage, PaymentsPage, SupplierFormPage, SuppliersPage, TransactionFormPage, TransactionsPage } from './accounts/AccountExtraPages';
import { BackendForgotPasswordPage, BackendLoginPage, BackendRegisterPage, BackendResetPasswordPage, BackendVerifyEmailPage } from './auth/BackendAuthPages';
import { BankAccountsFormPage, BankAccountsListPage } from './banks/BankAccountsPages';
import { BloodGroupsFormPage, BloodGroupsListPage } from './settings/BloodGroupPages';
import {
    CertificateFormPage,
    CertificateGeneratePage,
    CertificateListPage,
} from './certificate/CertificatePages';
import {
    CertificateUiCreatePage,
    CertificateUiGeneratePage,
    CertificateUiHomePage,
    CertificateUiListPage,
} from './certificate/CertificateUiPages';
import {
    NoticeBoardFormPage,
    NoticeBoardListPage,
    NoticeBoardTranslatePage,
    SmsCampaignPage,
    SmsMailCreatePage,
    SmsMailListPage,
    SmsTemplateFormPage,
    SmsTemplateListPage,
} from './communication/CommunicationPages';
import {
    ExamAssignCreatePage,
    ExamAssignEditPage,
    ExamAssignListPage,
    ExaminationHomePage,
    ExaminationSettingsPage,
    MarksGradesFormPage,
    MarksGradesListPage,
    MarksRegisterCreatePage,
    MarksRegisterEditPage,
    MarksRegisterListPage,
    MarksRegisterViewPage,
} from './examination/ExaminationPages';
import {
    FeesAssignmentFormPage,
    FeesAssignmentViewPage,
    FeesAmendmentsPage,
    FeesAssignmentsPage,
    FeesCollectionCreatePage,
    FeesCollectionCollectPage,
    FeesCollectionEditPage,
    FeesCollectionViewPage,
    FeesCancelledCollectPage,
    FeesCollectionsPage,
    FeesGroupFormPage,
    FeesGroupViewPage,
    FeesGroupsPage,
    FeesMasterFormPage,
    FeesMasterViewPage,
    FeesMastersPage,
    FeesOnlineTransactionsPage,
    FeesTransactionsPage,
    FeesTypeFormPage,
    FeesTypeViewPage,
    FeesTypesPage,
} from './fees/FeesModulePages';
import { DashboardPage } from './dashboard/DashboardPage';
import PublicHomePage from './public/pages/PublicHomePage';
import PublicAboutPage from './public/pages/PublicAboutPage';
import PublicNewsPage from './public/pages/PublicNewsPage';
import PublicNewsDetailPage from './public/pages/PublicNewsDetailPage';
import PublicEventsPage from './public/pages/PublicEventsPage';
import PublicEventDetailPage from './public/pages/PublicEventDetailPage';
import PublicNoticesPage from './public/pages/PublicNoticesPage';
import PublicNoticeDetailPage from './public/pages/PublicNoticeDetailPage';
import PublicContactPage from './public/pages/PublicContactPage';
import PublicResultPage from './public/pages/PublicResultPage';
import PublicDynamicPage from './public/pages/PublicDynamicPage';
import PublicOnlineAdmissionPage from './public/pages/PublicOnlineAdmissionPage';
import PublicOnlineAdmissionFeesPage from './public/pages/PublicOnlineAdmissionFeesPage';
import PublicLandingPage from './public/pages/PublicLandingPage';
import PublicPolicyPage from './public/pages/PublicPolicyPage';
import { FeesCollectionReportPage } from './reports/FeesCollectionReportPage';
import { FeesSummaryReportPage } from './reports/FeesSummaryReportPage';
import { BankReconciliationProcessPage, BankReconciliationReportPage } from './reports/BankReconciliationReportPage';
import { OutstandingBreakdownReportPage } from './reports/OutstandingBreakdownReportPage';
import { StudentsReportPage } from './reports/StudentsReportPage';
import { FeesByYearDetailPage, FeesByYearReportPage } from './reports/FeesByYearReportPage';
import {
    AccountReportPage,
    BoardingStudentsReportPage,
    DuplicateStudentsReportPage,
    MarksheetReportPage,
    MeritListReportPage,
} from './reports/AcademicReportPages';
import { ReportEntryPage } from './reports/ReportEntryPage';
import { ReportsHomePage } from './reports/ReportsHomePage';
import { CommunicationHomePage } from './communication/CommunicationHomePage';
import { SettingsHomePage } from './settings/SettingsHomePage';
import { SettingsPaymentGatewayPage } from './settings/SettingsPaymentGatewayPage';
import { SettingsEmailPage } from './settings/SettingsEmailPage';
import { SettingsGeneralPage } from './settings/SettingsGeneralPage';
import { SettingsNotificationPage } from './settings/SettingsNotificationPage';
import {
    SettingsRecaptchaPage,
    SettingsSmsPage,
    SettingsSoftwareUpdatePage,
    SettingsStoragePage,
    SettingsTaskSchedulersPage,
} from './settings/SettingsToolsPages';
import { StaffHomePage } from './staff/StaffHomePage';
import { UsersPage } from './staff/UsersPage';
import { UserFormPage } from './staff/UserFormPage';
import { RolesPage } from './staff/RolesPage';
import { DepartmentPage, DesignationPage } from './staff/StaffResourcePage';
import { StaffSalaryBatchPage } from './staff/StaffSalaryBatchPage';
import { ProfilePage } from './profile/ProfilePage';
import { ProfileEditPage } from './profile/ProfileEditPage';
import { PasswordUpdatePage } from './profile/PasswordUpdatePage';
import { StudentPanelDashboardPage } from './panels/StudentPanelDashboardPage';
import { ParentPanelDashboardPage } from './panels/ParentPanelDashboardPage';
import { DeletedHistoryPage } from './students/DeletedHistoryListPage';
import { DeletedHistoryShowPage } from './students/DeletedHistoryShowPage';
import { GendersFormPage, GendersListPage, GendersTranslatePage } from './settings/GenderPages';
import { GmeetFormPage, GmeetListPage } from './gmeet/GmeetPages';
import { GoodsHomePage } from './goods/GoodsPages';
import { HomeworkFormPage, HomeworkListPage } from './homework/HomeworkPages';
import { IdCardFormPage, IdCardGeneratePage, IdCardListPage } from './idcard/IdCardPages';
import { LibraryHomePage } from './library/LibraryPages';
import { LanguagesListPage, LanguageFormPage, LanguageTermsPage } from './languages/LanguagePages';
import { BackendViewHubPage, CommonViewHubPage, ErrorsHubPage, FrontendHubPage, GenericMigratedPage, PanelHubPage } from './legacy/LegacyViewMigrations';
import { EndpointDataPage } from './legacy/ConnectedLegacyPages';
import { PanelListPage } from './panels/PanelPages';
import { OnlineExamResultPage, OnlineExamViewPage } from './panels/PanelOnlineExamPages';
import { PanelPasswordPage, PanelProfileEditPage, PanelProfileViewPage } from './panels/PanelProfilePages';
import { MailEmailVerificationPage, MailForgotPasswordPage, MailResetPasswordPage } from './mail/MailPages';
import { OrdersCreatePage, OrdersListPage } from './orders/OrderPages';
import { ReligionsFormPage, ReligionsListPage, ReligionsTranslatePage } from './settings/ReligionPages';
import { SessionsFormPage, SessionsListPage, SessionsTranslatePage } from './settings/SessionPages';
import { AdminLayout } from './layout/AdminLayout';
import { StudentsPage } from './students/StudentsPage';
import {
    ParentFormPage,
    ParentShowPage,
    ParentsPage,
    PromoteStudentsCreatePage,
    PromoteStudentsIndexPage,
    StudentCategoriesPage,
    StudentCategoryFormPage,
    StudentCategoryShowPage,
    StudentCreatePage,
    StudentEditPage,
    StudentShowPage,
    StudentUploadPage,
    StudentUpdateFeesPage,
} from './students/StudentModulePages';

function RedirectToSpaCollect() {
    const { studentId } = useParams();
    return <Navigate to={`/collections/collect/${studentId}`} replace />;
}

function SpaNotFound() {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center gap-4 bg-slate-50 px-4 text-center">
            <p className="text-xl font-semibold text-slate-800">Page not found</p>
            <div className="flex gap-4 text-sm font-medium text-blue-700">
                <Link to="/">Public home</Link>
                <Link to="/login">Staff login</Link>
            </div>
        </div>
    );
}

export default function App() {
    return (
        <Routes>
            <Route path="/" element={<PublicHomePage />} />
            <Route path="/about" element={<PublicAboutPage />} />
            <Route path="/news" element={<PublicNewsPage />} />
            <Route path="/news-detail/:id" element={<PublicNewsDetailPage />} />
            <Route path="/events" element={<PublicEventsPage />} />
            <Route path="/event-detail/:id" element={<PublicEventDetailPage />} />
            <Route path="/notices" element={<PublicNoticesPage />} />
            <Route path="/notice-detail/:id" element={<PublicNoticeDetailPage />} />
            <Route path="/contact" element={<PublicContactPage />} />
            <Route path="/result" element={<PublicResultPage />} />
            <Route path="/page/:slug" element={<PublicDynamicPage />} />
            <Route path="/online-admission" element={<PublicOnlineAdmissionPage />} />
            <Route path="/online-admission-fees/:reference/:admissionId" element={<PublicOnlineAdmissionFeesPage />} />
            <Route path="/landing" element={<PublicLandingPage />} />
            <Route path="/policy" element={<PublicPolicyPage />} />
            <Route path="/login" element={<BackendLoginPage />} />
            <Route path="/register" element={<BackendRegisterPage />} />
            <Route path="/forgot-password" element={<BackendForgotPasswordPage />} />
            <Route path="/reset-password/:email/:token" element={<BackendResetPasswordPage />} />
            <Route path="/verify-email/:email/:token" element={<BackendVerifyEmailPage />} />
            <Route path="/mail/forgot-password" element={<MailForgotPasswordPage />} />
            <Route path="/mail/reset-password/:email/:token" element={<MailResetPasswordPage />} />
            <Route path="/mail/email-verification" element={<MailEmailVerificationPage />} />
            <Route path="/student-panel" element={<StudentPanelDashboardPage />} />
            <Route path="/parent-panel" element={<ParentPanelDashboardPage />} />
            <Route path="/dashboard" element={<DashboardPage Layout={AdminLayout} />} />
            <Route path="/users" element={<UsersPage />} />
            <Route path="/users/create" element={<UserFormPage />} />
            <Route path="/users/:id/edit" element={<UserFormPage edit />} />
            <Route path="/roles" element={<RolesPage />} />
            <Route path="/students" element={<StudentsPage />} />
            <Route path="/qr_code" element={<StudentsPage />} />
            <Route path="/students/create" element={<StudentCreatePage />} />
            <Route path="/students/upload" element={<StudentUploadPage />} />
            <Route path="/students/update-fees" element={<StudentUpdateFeesPage />} />
            <Route path="/students/:id" element={<StudentShowPage />} />
            <Route path="/students/:id/edit" element={<StudentEditPage />} />
            <Route path="/categories" element={<StudentCategoriesPage />} />
            <Route path="/categories/create" element={<StudentCategoryFormPage />} />
            <Route path="/categories/:id/edit" element={<StudentCategoryFormPage edit />} />
            <Route path="/categories/:id" element={<StudentCategoryShowPage />} />
            <Route path="/deleted-history" element={<DeletedHistoryPage />} />
            <Route path="/deleted-history/:id" element={<DeletedHistoryShowPage />} />
            <Route path="/fees" element={<Navigate to="/collections" replace />} />
            <Route path="/fees/collections" element={<Navigate to="/collections" replace />} />
            <Route path="/fees/collections/cancelled" element={<Navigate to="/collections/cancelled" replace />} />
            <Route path="/fees/collections/create" element={<Navigate to="/collections/create" replace />} />
            <Route path="/fees/collections/collect/:studentId" element={<RedirectToSpaCollect />} />
            <Route path="/fees/collections/:id" element={<FeesCollectionViewPage Layout={AdminLayout} />} />
            <Route path="/fees/collections/:id/edit" element={<FeesCollectionEditPage Layout={AdminLayout} />} />
            <Route path="/fees/assignments" element={<Navigate to="/assignments" replace />} />
            <Route path="/fees/assignments/create" element={<Navigate to="/assignments/create" replace />} />
            <Route path="/fees/assignments/:id" element={<FeesAssignmentViewPage Layout={AdminLayout} />} />
            <Route path="/fees/assignments/:id/edit" element={<FeesAssignmentFormPage Layout={AdminLayout} edit />} />
            <Route path="/fees/types" element={<Navigate to="/types" replace />} />
            <Route path="/fees/types/create" element={<Navigate to="/types/create" replace />} />
            <Route path="/fees/types/:id" element={<FeesTypeViewPage Layout={AdminLayout} />} />
            <Route path="/fees/types/:id/edit" element={<FeesTypeFormPage Layout={AdminLayout} edit />} />
            <Route path="/fees/groups" element={<Navigate to="/groups" replace />} />
            <Route path="/fees/groups/create" element={<Navigate to="/groups/create" replace />} />
            <Route path="/fees/groups/:id" element={<FeesGroupViewPage Layout={AdminLayout} />} />
            <Route path="/fees/groups/:id/edit" element={<FeesGroupFormPage Layout={AdminLayout} edit />} />
            <Route path="/fees/masters" element={<Navigate to="/masters" replace />} />
            <Route path="/fees/masters/create" element={<Navigate to="/masters/create" replace />} />
            <Route path="/fees/masters/:id" element={<FeesMasterViewPage Layout={AdminLayout} />} />
            <Route path="/fees/masters/:id/edit" element={<FeesMasterFormPage Layout={AdminLayout} edit />} />
            <Route path="/fees/transactions" element={<Navigate to="/transactions" replace />} />
            <Route path="/fees/online-transactions" element={<Navigate to="/online-transactions" replace />} />
            <Route path="/fees/amendments" element={<Navigate to="/amendments" replace />} />
            <Route path="/collections" element={<FeesCollectionsPage Layout={AdminLayout} />} />
            <Route path="/collections/cancelled" element={<FeesCancelledCollectPage Layout={AdminLayout} />} />
            <Route path="/collections/create" element={<FeesCollectionCreatePage Layout={AdminLayout} />} />
            <Route path="/collections/collect/:studentId" element={<FeesCollectionCollectPage Layout={AdminLayout} />} />
            <Route path="/collections/:id/edit" element={<FeesCollectionEditPage Layout={AdminLayout} />} />
            <Route path="/collections/:id" element={<FeesCollectionViewPage Layout={AdminLayout} />} />
            <Route path="/assignments" element={<FeesAssignmentsPage Layout={AdminLayout} />} />
            <Route path="/assignments/create" element={<FeesAssignmentFormPage Layout={AdminLayout} />} />
            <Route path="/assignments/:id" element={<FeesAssignmentViewPage Layout={AdminLayout} />} />
            <Route path="/assignments/:id/edit" element={<FeesAssignmentFormPage Layout={AdminLayout} edit />} />
            <Route path="/types" element={<FeesTypesPage Layout={AdminLayout} />} />
            <Route path="/types/create" element={<FeesTypeFormPage Layout={AdminLayout} />} />
            <Route path="/types/:id" element={<FeesTypeViewPage Layout={AdminLayout} />} />
            <Route path="/types/:id/edit" element={<FeesTypeFormPage Layout={AdminLayout} edit />} />
            <Route path="/groups" element={<FeesGroupsPage Layout={AdminLayout} />} />
            <Route path="/groups/create" element={<FeesGroupFormPage Layout={AdminLayout} />} />
            <Route path="/groups/:id" element={<FeesGroupViewPage Layout={AdminLayout} />} />
            <Route path="/groups/:id/edit" element={<FeesGroupFormPage Layout={AdminLayout} edit />} />
            <Route path="/masters" element={<FeesMastersPage Layout={AdminLayout} />} />
            <Route path="/masters/create" element={<FeesMasterFormPage Layout={AdminLayout} />} />
            <Route path="/masters/:id" element={<FeesMasterViewPage Layout={AdminLayout} />} />
            <Route path="/masters/:id/edit" element={<FeesMasterFormPage Layout={AdminLayout} edit />} />
            <Route path="/transactions" element={<FeesTransactionsPage Layout={AdminLayout} />} />
            <Route path="/online-transactions" element={<FeesOnlineTransactionsPage Layout={AdminLayout} />} />
            <Route path="/amendments" element={<FeesAmendmentsPage Layout={AdminLayout} />} />
            <Route path="/examination" element={<ExaminationHomePage Layout={AdminLayout} />} />
            <Route path="/examination/marks-grades" element={<MarksGradesListPage Layout={AdminLayout} />} />
            <Route path="/examination/marks-grades/create" element={<MarksGradesFormPage Layout={AdminLayout} />} />
            <Route path="/examination/marks-grades/:id/edit" element={<MarksGradesFormPage Layout={AdminLayout} edit />} />
            <Route path="/examination/settings" element={<ExaminationSettingsPage Layout={AdminLayout} />} />
            <Route path="/examination/exam-assign" element={<ExamAssignListPage Layout={AdminLayout} />} />
            <Route path="/examination/exam-assign/create" element={<ExamAssignCreatePage Layout={AdminLayout} />} />
            <Route path="/examination/exam-assign/:id/edit" element={<ExamAssignEditPage Layout={AdminLayout} />} />
            <Route path="/examination/marks-register" element={<MarksRegisterListPage Layout={AdminLayout} />} />
            <Route path="/examination/marks-register/create" element={<MarksRegisterCreatePage Layout={AdminLayout} />} />
            <Route path="/examination/marks-register/:id/view" element={<MarksRegisterViewPage Layout={AdminLayout} />} />
            <Route path="/examination/marks-register/:id/edit" element={<MarksRegisterEditPage Layout={AdminLayout} />} />
            <Route path="/settings/genders" element={<GendersListPage Layout={AdminLayout} />} />
            <Route path="/settings/genders/create" element={<GendersFormPage Layout={AdminLayout} />} />
            <Route path="/settings/genders/:id/edit" element={<GendersFormPage Layout={AdminLayout} edit />} />
            <Route path="/settings/genders/:id/translate" element={<GendersTranslatePage Layout={AdminLayout} />} />
            <Route path="/settings/religions" element={<ReligionsListPage Layout={AdminLayout} />} />
            <Route path="/settings/religions/create" element={<ReligionsFormPage Layout={AdminLayout} />} />
            <Route path="/settings/religions/:id/edit" element={<ReligionsFormPage Layout={AdminLayout} edit />} />
            <Route path="/settings/religions/:id/translate" element={<ReligionsTranslatePage Layout={AdminLayout} />} />
            <Route path="/settings/sessions" element={<SessionsListPage Layout={AdminLayout} />} />
            <Route path="/settings/sessions/create" element={<SessionsFormPage Layout={AdminLayout} />} />
            <Route path="/settings/sessions/:id/edit" element={<SessionsFormPage Layout={AdminLayout} edit />} />
            <Route path="/settings/sessions/:id/translate" element={<SessionsTranslatePage Layout={AdminLayout} />} />
            <Route path="/orders" element={<OrdersListPage Layout={AdminLayout} />} />
            <Route path="/orders/create" element={<OrdersCreatePage Layout={AdminLayout} />} />
            <Route path="/liveclass/gmeet" element={<GmeetListPage Layout={AdminLayout} />} />
            <Route path="/liveclass/gmeet/create" element={<GmeetFormPage Layout={AdminLayout} />} />
            <Route path="/liveclass/gmeet/:id/edit" element={<GmeetFormPage Layout={AdminLayout} edit />} />
            <Route path="/homework" element={<HomeworkListPage Layout={AdminLayout} />} />
            <Route path="/homework/create" element={<HomeworkFormPage Layout={AdminLayout} />} />
            <Route path="/homework/:id/edit" element={<HomeworkFormPage Layout={AdminLayout} edit />} />
            <Route path="/goods" element={<GoodsHomePage Layout={AdminLayout} />} />
            <Route path="/idcard" element={<IdCardListPage Layout={AdminLayout} />} />
            <Route path="/idcard/create" element={<IdCardFormPage Layout={AdminLayout} />} />
            <Route path="/idcard/:id/edit" element={<IdCardFormPage Layout={AdminLayout} edit />} />
            <Route path="/idcard/generate" element={<IdCardGeneratePage Layout={AdminLayout} />} />
            <Route path="/languages" element={<LanguagesListPage Layout={AdminLayout} />} />
            <Route path="/languages/create" element={<LanguageFormPage Layout={AdminLayout} />} />
            <Route path="/languages/:id/edit" element={<LanguageFormPage Layout={AdminLayout} edit />} />
            <Route path="/languages/:id/terms" element={<LanguageTermsPage Layout={AdminLayout} />} />
            <Route path="/library" element={<LibraryHomePage Layout={AdminLayout} />} />
            <Route path="/library/create" element={<LibraryHomePage Layout={AdminLayout} />} />
            <Route path="/library/:id/edit" element={<LibraryHomePage Layout={AdminLayout} />} />
            <Route path="/parents" element={<ParentsPage />} />
            <Route path="/parents/create" element={<ParentFormPage />} />
            <Route path="/parents/:id/edit" element={<ParentFormPage edit />} />
            <Route path="/parents/:id" element={<ParentShowPage />} />
            <Route path="/promote" element={<PromoteStudentsIndexPage />} />
            <Route path="/promote/create" element={<PromoteStudentsCreatePage />} />
            <Route path="/academic" element={<Navigate to="/classes" replace />} />
            <Route
                path="/classes"
                element={
                    <AcademicListPage
                        Layout={AdminLayout}
                        title=""
                        endpoint="/classes"
                        createTo="/classes/create"
                        editBase="/classes"
                        viewBase="/classes"
                        listColumns={CLASSES_LIST_COLUMNS}
                        tabViews={[
                            {
                                id: 'classes',
                                label: 'Class',
                                title: '',
                                endpoint: '/classes',
                                createTo: '/classes/create',
                                editBase: '/classes',
                                viewBase: '/classes',
                                listColumns: CLASSES_LIST_COLUMNS,
                            },
                            {
                                id: 'sections',
                                label: 'Section',
                                title: '',
                                endpoint: '/section',
                                createTo: '/sections/create',
                                editBase: '/sections',
                                viewBase: '/sections',
                            },
                            {
                                id: 'class-setup',
                                label: 'Class Setup',
                                title: '',
                                endpoint: '/class-setup',
                                createTo: '/class-setups/create',
                                editBase: '/class-setups',
                                viewBase: '/class-setups',
                            },
                        ]}
                    />
                }
            />
            <Route path="/classes/create" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Class" titleEdit="Edit Class" loadEndpoint="/classes" storeEndpoint="/classes/store" updateEndpoint="/classes/update" backTo="/classes" />} />
            <Route path="/classes/:id/edit" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Class" titleEdit="Edit Class" loadEndpoint="/classes" storeEndpoint="/classes/store" updateEndpoint="/classes/update" backTo="/classes" />} />
            <Route path="/classes/:id" element={<AcademicViewPage Layout={AdminLayout} title="Class Details" loadEndpoint="/classes" backTo="/classes" editBase="/classes" />} />
            <Route path="/sections" element={<AcademicListPage Layout={AdminLayout} title="" endpoint="/section" createTo="/sections/create" editBase="/sections" viewBase="/sections" />} />
            <Route path="/sections/create" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Section" titleEdit="Edit Section" loadEndpoint="/section" storeEndpoint="/section/store" updateEndpoint="/section/update" backTo="/sections" />} />
            <Route path="/sections/:id/edit" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Section" titleEdit="Edit Section" loadEndpoint="/section" storeEndpoint="/section/store" updateEndpoint="/section/update" backTo="/sections" />} />
            <Route path="/sections/:id" element={<AcademicViewPage Layout={AdminLayout} title="Section Details" loadEndpoint="/section" backTo="/sections" editBase="/sections" />} />
            <Route
                path="/subjects"
                element={
                    <AcademicListPage
                        Layout={AdminLayout}
                        title=""
                        endpoint="/subject"
                        createTo="/subjects/create"
                        editBase="/subjects"
                        viewBase="/subjects"
                        tabViews={[
                            {
                                id: 'subjects',
                                label: 'Subject',
                                title: '',
                                endpoint: '/subject',
                                createTo: '/subjects/create',
                                editBase: '/subjects',
                                viewBase: '/subjects',
                            },
                            {
                                id: 'subject-assigns',
                                label: 'Subject Assign',
                                title: '',
                                endpoint: '/assign-subject',
                                createTo: '/subject-assigns/create',
                                editBase: '/subject-assigns',
                                viewBase: '/subject-assigns',
                            },
                            {
                                id: 'time-schedules',
                                label: 'Time Schedule',
                                title: '',
                                endpoint: '/time/schedule',
                                createTo: '/time-schedules/create',
                                editBase: '/time-schedules',
                                viewBase: '/time-schedules',
                            },
                        ]}
                    />
                }
            />
            <Route path="/subjects/create" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Subject" titleEdit="Edit Subject" loadEndpoint="/subject" storeEndpoint="/subject/store" updateEndpoint="/subject/update" backTo="/subjects" />} />
            <Route path="/subjects/:id/edit" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Subject" titleEdit="Edit Subject" loadEndpoint="/subject" storeEndpoint="/subject/store" updateEndpoint="/subject/update" backTo="/subjects" />} />
            <Route path="/subjects/:id" element={<AcademicViewPage Layout={AdminLayout} title="Subject Details" loadEndpoint="/subject" backTo="/subjects" editBase="/subjects" />} />
            <Route path="/shifts" element={<AcademicListPage Layout={AdminLayout} title="Shifts" endpoint="/shift" createTo="/shifts/create" editBase="/shifts" viewBase="/shifts" />} />
            <Route path="/shifts/create" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Shift" titleEdit="Edit Shift" loadEndpoint="/shift" storeEndpoint="/shift/store" updateEndpoint="/shift/update" backTo="/shifts" />} />
            <Route path="/shifts/:id/edit" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Shift" titleEdit="Edit Shift" loadEndpoint="/shift" storeEndpoint="/shift/store" updateEndpoint="/shift/update" backTo="/shifts" />} />
            <Route path="/shifts/:id" element={<AcademicViewPage Layout={AdminLayout} title="Shift Details" loadEndpoint="/shift" backTo="/shifts" editBase="/shifts" />} />
            <Route
                path="/class-rooms"
                element={
                    <AcademicListPage
                        Layout={AdminLayout}
                        title=""
                        endpoint="/class-room"
                        createTo="/class-rooms/create"
                        editBase="/class-rooms"
                        viewBase="/class-rooms"
                        tabViews={[
                            {
                                id: 'class-rooms',
                                label: 'Class rooms',
                                title: '',
                                endpoint: '/class-room',
                                createTo: '/class-rooms/create',
                                editBase: '/class-rooms',
                                viewBase: '/class-rooms',
                            },
                            {
                                id: 'class-routines',
                                label: 'Class routine',
                                title: '',
                                endpoint: '/class-routine',
                                createTo: '/class-routines/create',
                                editBase: '/class-routines',
                                viewBase: '/class-routines',
                            },
                            {
                                id: 'exam-routines',
                                label: 'Exam routine',
                                title: '',
                                endpoint: '/exam-routine',
                                createTo: '/exam-routines/create',
                                editBase: '/exam-routines',
                                viewBase: '/exam-routines',
                            },
                        ]}
                    />
                }
            />
            <Route path="/class-rooms/create" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Class Room" titleEdit="Edit Class Room" loadEndpoint="/class-room" storeEndpoint="/class-room/store" updateEndpoint="/class-room/update" backTo="/class-rooms" />} />
            <Route path="/class-rooms/:id/edit" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Class Room" titleEdit="Edit Class Room" loadEndpoint="/class-room" storeEndpoint="/class-room/store" updateEndpoint="/class-room/update" backTo="/class-rooms" />} />
            <Route path="/class-rooms/:id" element={<AcademicViewPage Layout={AdminLayout} title="Class Room Details" loadEndpoint="/class-room" backTo="/class-rooms" editBase="/class-rooms" />} />
            <Route path="/class-setups" element={<AcademicListPage Layout={AdminLayout} title="Class Setups" endpoint="/class-setup" createTo="/class-setups/create" editBase="/class-setups" viewBase="/class-setups" />} />
            <Route path="/class-setups/create" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Class Setup" titleEdit="Edit Class Setup" loadEndpoint="/class-setup" storeEndpoint="/class-setup/store" updateEndpoint="/class-setup/update" backTo="/class-setups" />} />
            <Route path="/class-setups/:id/edit" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Class Setup" titleEdit="Edit Class Setup" loadEndpoint="/class-setup" storeEndpoint="/class-setup/store" updateEndpoint="/class-setup/update" backTo="/class-setups" />} />
            <Route path="/class-setups/:id" element={<AcademicViewPage Layout={AdminLayout} title="Class Setup Details" loadEndpoint="/class-setup" backTo="/class-setups" editBase="/class-setups" />} />
            <Route path="/subject-assigns" element={<AcademicListPage Layout={AdminLayout} title="" endpoint="/assign-subject" createTo="/subject-assigns/create" editBase="/subject-assigns" viewBase="/subject-assigns" />} />
            <Route path="/subject-assigns/create" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Subject Assign" titleEdit="Edit Subject Assign" loadEndpoint="/assign-subject" storeEndpoint="/assign-subject/store" updateEndpoint="/assign-subject/update" backTo="/subject-assigns" />} />
            <Route path="/subject-assigns/:id/edit" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Subject Assign" titleEdit="Edit Subject Assign" loadEndpoint="/assign-subject" storeEndpoint="/assign-subject/store" updateEndpoint="/assign-subject/update" backTo="/subject-assigns" />} />
            <Route path="/subject-assigns/:id" element={<AcademicViewPage Layout={AdminLayout} title="Subject Assign Details" loadEndpoint="/assign-subject" backTo="/subject-assigns" editBase="/subject-assigns" />} />
            <Route path="/time-schedules" element={<AcademicListPage Layout={AdminLayout} title="" endpoint="/time/schedule" createTo="/time-schedules/create" editBase="/time-schedules" viewBase="/time-schedules" />} />
            <Route path="/time-schedules/create" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Time Schedule" titleEdit="Edit Time Schedule" loadEndpoint="/time/schedule" storeEndpoint="/time/schedule/store" updateEndpoint="/time/schedule/update" backTo="/time-schedules" />} />
            <Route path="/time-schedules/:id/edit" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Time Schedule" titleEdit="Edit Time Schedule" loadEndpoint="/time/schedule" storeEndpoint="/time/schedule/store" updateEndpoint="/time/schedule/update" backTo="/time-schedules" />} />
            <Route path="/time-schedules/:id" element={<AcademicViewPage Layout={AdminLayout} title="Time Schedule Details" loadEndpoint="/time/schedule" backTo="/time-schedules" editBase="/time-schedules" />} />
            <Route path="/class-routines" element={<AcademicListPage Layout={AdminLayout} title="" endpoint="/class-routine" createTo="/class-routines/create" editBase="/class-routines" viewBase="/class-routines" />} />
            <Route path="/class-routines/create" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Class Routine" titleEdit="Edit Class Routine" loadEndpoint="/class-routine" storeEndpoint="/class-routine/store" updateEndpoint="/class-routine/update" backTo="/class-routines" />} />
            <Route path="/class-routines/:id/edit" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Class Routine" titleEdit="Edit Class Routine" loadEndpoint="/class-routine" storeEndpoint="/class-routine/store" updateEndpoint="/class-routine/update" backTo="/class-routines" />} />
            <Route path="/class-routines/:id" element={<AcademicViewPage Layout={AdminLayout} title="Class Routine Details" loadEndpoint="/class-routine" backTo="/class-routines" editBase="/class-routines" />} />
            <Route path="/exam-routines" element={<AcademicListPage Layout={AdminLayout} title="" endpoint="/exam-routine" createTo="/exam-routines/create" editBase="/exam-routines" viewBase="/exam-routines" />} />
            <Route path="/exam-routines/create" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Exam Routine" titleEdit="Edit Exam Routine" loadEndpoint="/exam-routine" storeEndpoint="/exam-routine/store" updateEndpoint="/exam-routine/update" backTo="/exam-routines" />} />
            <Route path="/exam-routines/:id/edit" element={<AcademicFormPage Layout={AdminLayout} titleCreate="Create Exam Routine" titleEdit="Edit Exam Routine" loadEndpoint="/exam-routine" storeEndpoint="/exam-routine/store" updateEndpoint="/exam-routine/update" backTo="/exam-routines" />} />
            <Route path="/exam-routines/:id" element={<AcademicViewPage Layout={AdminLayout} title="Exam Routine Details" loadEndpoint="/exam-routine" backTo="/exam-routines" editBase="/exam-routines" />} />
            <Route path="/accounting" element={<AccountsHomePage Layout={AdminLayout} />} />
            <Route path="/chart-of-accounts" element={<ChartOfAccountsPage Layout={AdminLayout} />} />
            <Route path="/chart-of-accounts/create" element={<ChartOfAccountsFormPage Layout={AdminLayout} />} />
            <Route path="/chart-of-accounts/:id" element={<ChartOfAccountsViewPage Layout={AdminLayout} />} />
            <Route path="/chart-of-accounts/:id/edit" element={<ChartOfAccountsFormPage Layout={AdminLayout} edit />} />
            <Route path="/payment-methods" element={<PaymentMethodsPage Layout={AdminLayout} />} />
            <Route path="/payment-methods/create" element={<PaymentMethodFormPage Layout={AdminLayout} />} />
            <Route path="/payment-methods/:id" element={<PaymentMethodViewPage Layout={AdminLayout} />} />
            <Route path="/payment-methods/:id/edit" element={<PaymentMethodFormPage Layout={AdminLayout} edit />} />
            <Route path="/account-heads" element={<AccountHeadsPage Layout={AdminLayout} />} />
            <Route path="/account-heads/create" element={<AccountHeadFormPage Layout={AdminLayout} />} />
            <Route path="/account-heads/:id" element={<AccountHeadViewPage Layout={AdminLayout} />} />
            <Route path="/account-heads/:id/edit" element={<AccountHeadFormPage Layout={AdminLayout} edit />} />
            <Route path="/income" element={<IncomePage Layout={AdminLayout} />} />
            <Route path="/income/create" element={<IncomeFormPage Layout={AdminLayout} />} />
            <Route path="/income/:id/edit" element={<IncomeFormPage Layout={AdminLayout} edit />} />
            <Route path="/expense" element={<ExpensePage Layout={AdminLayout} />} />
            <Route path="/expense/create" element={<ExpenseFormPage Layout={AdminLayout} />} />
            <Route path="/expense/:id/edit" element={<ExpenseFormPage Layout={AdminLayout} edit />} />
            <Route path="/deposits" element={<DepositsPage Layout={AdminLayout} />} />
            <Route path="/deposits/create" element={<DepositFormPage Layout={AdminLayout} />} />
            <Route path="/deposits/:id/edit" element={<DepositFormPage Layout={AdminLayout} />} />
            <Route path="/payments" element={<PaymentsPage Layout={AdminLayout} />} />
            <Route path="/payments/create" element={<PaymentFormPage Layout={AdminLayout} />} />
            <Route path="/payments/:id/edit" element={<PaymentFormPage Layout={AdminLayout} />} />
            <Route path="/account-transactions" element={<TransactionsPage Layout={AdminLayout} />} />
            <Route path="/account-transactions/create" element={<TransactionFormPage Layout={AdminLayout} />} />
            <Route path="/account-transactions/:id/edit" element={<TransactionFormPage Layout={AdminLayout} />} />
            <Route path="/suppliers" element={<SuppliersPage Layout={AdminLayout} />} />
            <Route path="/suppliers/create" element={<SupplierFormPage Layout={AdminLayout} />} />
            <Route path="/suppliers/:id/edit" element={<SupplierFormPage Layout={AdminLayout} />} />
            <Route path="/invoices" element={<InvoicesPage Layout={AdminLayout} />} />
            <Route path="/invoices/create" element={<InvoiceFormPage Layout={AdminLayout} />} />
            <Route path="/invoices/:id/edit" element={<InvoiceFormPage Layout={AdminLayout} />} />
            <Route path="/cash" element={<CashPage Layout={AdminLayout} />} />
            <Route path="/cash/create" element={<CashFormPage Layout={AdminLayout} />} />
            <Route path="/cash/:id/edit" element={<CashFormPage Layout={AdminLayout} edit />} />
            <Route path="/product" element={<ProductPage Layout={AdminLayout} />} />
            <Route path="/product/create" element={<ProductCreatePage Layout={AdminLayout} />} />
            <Route path="/product/:id/edit" element={<ProductCreatePage Layout={AdminLayout} edit />} />
            <Route path="/product/sell" element={<ProductSellPage Layout={AdminLayout} />} />
            <Route path="/item" element={<ItemPage Layout={AdminLayout} />} />
            <Route path="/item/create" element={<ItemCreatePage Layout={AdminLayout} />} />
            <Route path="/item/:id/edit" element={<ItemCreatePage Layout={AdminLayout} edit />} />
            <Route path="/attendance" element={<AttendanceIndexPage Layout={AdminLayout} />} />
            <Route path="/attendance/report" element={<AttendanceReportPage Layout={AdminLayout} />} />
            <Route path="/attendance/notification" element={<AttendanceNotificationPage Layout={AdminLayout} />} />
            <Route path="/communication" element={<CommunicationHomePage />} />
            <Route path="/staff" element={<StaffHomePage />} />
            <Route path="/settings" element={<SettingsHomePage />} />
            <Route path="/reports" element={<ReportsHomePage />} />
            <Route path="/reports/marksheet" element={<MarksheetReportPage />} />
            <Route path="/reports/merit-list" element={<MeritListReportPage />} />
            <Route path="/reports/progress-card" element={<ReportEntryPage title="Progress Card Report" endpoint="/report-progress-card" />} />
            <Route path="/reports/due-fees" element={<ReportEntryPage title="Due Fees Report" endpoint="/report-due-fees" />} />
            <Route path="/reports/class-routine" element={<ReportEntryPage title="Class Routine Report" endpoint="/report-class-routine" />} />
            <Route path="/reports/exam-routine" element={<ReportEntryPage title="Exam Routine Report" endpoint="/report-exam-routine" />} />
            <Route path="/reports/duplicate-students" element={<DuplicateStudentsReportPage />} />
            <Route path="/reports/account" element={<AccountReportPage />} />
            <Route path="/reports/accounting/income" element={<ReportEntryPage title="Accounting Income Report" endpoint="/accounting/reports/income" />} />
            <Route path="/reports/accounting/expense" element={<ReportEntryPage title="Accounting Expense Report" endpoint="/accounting/reports/expense" />} />
            <Route path="/reports/accounting/profit-loss" element={<ReportEntryPage title="Accounting Profit/Loss Report" endpoint="/accounting/reports/profit-loss" />} />
            <Route path="/accounting/dashboard" element={<AccountingDashboardPage Layout={AdminLayout} />} />
            <Route path="/reports/accounting/dashboard" element={<Navigate to="/accounting/dashboard" replace />} />
            <Route path="/reports/accounting/cashbook" element={<ReportEntryPage title="Accounting Cashbook" endpoint="/accounting/cashbook" />} />
            <Route path="/reports/accounting/audit-log" element={<ReportEntryPage title="Accounting Audit Log" endpoint="/accounting/audit-log" />} />
            <Route path="/reports/fees-collection" element={<FeesCollectionReportPage />} />
            <Route path="/reports/outstanding-breakdown" element={<OutstandingBreakdownReportPage />} />
            <Route path="/reports/fees-summary" element={<FeesSummaryReportPage />} />
            <Route path="/reports/students" element={<StudentsReportPage />} />
            <Route path="/reports/fees-by-year" element={<FeesByYearReportPage />} />
            <Route path="/reports/fees-by-year/:studentId" element={<FeesByYearDetailPage />} />
            <Route path="/reports/boarding-students" element={<BoardingStudentsReportPage />} />
            <Route path="/reports/boarding-students/missing-2026" element={<ReportEntryPage title="Missing Boarding Students 2026" endpoint="/report-boarding-students/find-missing-2026" />} />
            <Route path="/reports/accounting/bank-reconciliation" element={<BankReconciliationReportPage />} />
            <Route path="/reports/accounting/bank-reconciliation/process" element={<BankReconciliationProcessPage />} />
            <Route path="/my/profile" element={<ProfilePage />} />
            <Route path="/my/profile/edit" element={<ProfileEditPage />} />
            <Route path="/my/password/update" element={<PasswordUpdatePage />} />
            <Route path="/settings/general" element={<SettingsGeneralPage />} />
            <Route path="/settings/notification" element={<SettingsNotificationPage />} />
            <Route path="/settings/storage" element={<SettingsStoragePage />} />
            <Route path="/settings/task-schedulers" element={<SettingsTaskSchedulersPage />} />
            <Route path="/settings/software-update" element={<SettingsSoftwareUpdatePage />} />
            <Route path="/settings/recaptcha" element={<SettingsRecaptchaPage />} />
            <Route path="/settings/sms" element={<SettingsSmsPage />} />
            <Route path="/settings/payment-gateway" element={<SettingsPaymentGatewayPage />} />
            <Route path="/settings/email" element={<SettingsEmailPage />} />
            <Route path="/staff/department" element={<DepartmentPage />} />
            <Route path="/staff/batch-processing" element={<StaffSalaryBatchPage />} />
            <Route path="/staff/designation" element={<DesignationPage />} />
            <Route path="/banks-accounts" element={<BankAccountsListPage Layout={AdminLayout} />} />
            <Route path="/banks-accounts/create" element={<BankAccountsFormPage Layout={AdminLayout} />} />
            <Route path="/banks-accounts/:id/edit" element={<BankAccountsFormPage Layout={AdminLayout} edit />} />
            <Route path="/blood-groups" element={<BloodGroupsListPage Layout={AdminLayout} />} />
            <Route path="/blood-groups/create" element={<BloodGroupsFormPage Layout={AdminLayout} />} />
            <Route path="/blood-groups/:id/edit" element={<BloodGroupsFormPage Layout={AdminLayout} edit />} />
            <Route path="/certificate" element={<CertificateListPage Layout={AdminLayout} />} />
            <Route path="/certificate/create" element={<CertificateFormPage Layout={AdminLayout} />} />
            <Route path="/certificate/:id/edit" element={<CertificateFormPage Layout={AdminLayout} edit />} />
            <Route path="/certificate/generate" element={<CertificateGeneratePage Layout={AdminLayout} />} />
            <Route path="/certificate-ui" element={<CertificateUiHomePage Layout={AdminLayout} />} />
            <Route path="/certificate-ui/list" element={<CertificateUiListPage Layout={AdminLayout} />} />
            <Route path="/certificate-ui/create" element={<CertificateUiCreatePage Layout={AdminLayout} />} />
            <Route path="/certificate-ui/generate" element={<CertificateUiGeneratePage Layout={AdminLayout} />} />
            <Route path="/communication/notice-board" element={<NoticeBoardListPage Layout={AdminLayout} />} />
            <Route path="/communication/notice-board/create" element={<NoticeBoardFormPage Layout={AdminLayout} />} />
            <Route path="/communication/notice-board/:id/edit" element={<NoticeBoardFormPage Layout={AdminLayout} edit />} />
            <Route path="/communication/notice-board/:id/translate" element={<NoticeBoardTranslatePage Layout={AdminLayout} />} />
            <Route path="/communication/template" element={<SmsTemplateListPage Layout={AdminLayout} />} />
            <Route path="/communication/template/create" element={<SmsTemplateFormPage Layout={AdminLayout} />} />
            <Route path="/communication/template/:id/edit" element={<SmsTemplateFormPage Layout={AdminLayout} edit />} />
            <Route path="/communication/smsmail" element={<SmsMailListPage Layout={AdminLayout} />} />
            <Route path="/communication/smsmail/create" element={<SmsMailCreatePage Layout={AdminLayout} />} />
            <Route path="/communication/smsmail/campaign" element={<SmsCampaignPage Layout={AdminLayout} />} />
            <Route path="/backend" element={<BackendViewHubPage Layout={AdminLayout} />} />
            <Route path="/backend/dashboard" element={<BackendDashboardPage Layout={AdminLayout} />} />
            <Route path="/backend/dashboard-pdf" element={<BackendDashboardPdfPage Layout={AdminLayout} />} />
            <Route path="/backend/dashboardtable" element={<BackendDashboardTablePage Layout={AdminLayout} />} />
            <Route path="/backend/master" element={<BackendMasterPage Layout={AdminLayout} />} />
            <Route path="/backend/menu-autocomplete" element={<BackendMenuAutocompletePage Layout={AdminLayout} />} />
            <Route path="/common" element={<CommonViewHubPage Layout={AdminLayout} />} />
            <Route path="/common/pagination" element={<GenericMigratedPage Layout={AdminLayout} title="Common Pagination" description="Hand-built SPA page replacing common/pagination.blade.php." />} />
            <Route path="/components" element={<CommonViewHubPage Layout={AdminLayout} />} />
            <Route path="/components/sidebar-header" element={<GenericMigratedPage Layout={AdminLayout} title="Component Sidebar Header" description="Hand-built SPA page replacing components/sidebar-header.blade.php." />} />
            <Route path="/components/certificate-generate" element={<GenericMigratedPage Layout={AdminLayout} title="Component Certificate Generate" description="Hand-built SPA page replacing components/certificate-generate.blade.php." />} />
            <Route path="/emails" element={<CommonViewHubPage Layout={AdminLayout} />} />
            <Route path="/emails/daily-report" element={<GenericMigratedPage Layout={AdminLayout} title="Email Daily Report" description="Hand-built SPA preview replacing emails/daily_report.blade.php." />} />
            <Route path="/errors" element={<ErrorsHubPage Layout={AdminLayout} />} />
            <Route path="/errors/400" element={<GenericMigratedPage Layout={AdminLayout} title="400 Error" description="Hand-built SPA error page." />} />
            <Route path="/errors/403" element={<GenericMigratedPage Layout={AdminLayout} title="403 Error" description="Hand-built SPA error page." />} />
            <Route path="/errors/404" element={<GenericMigratedPage Layout={AdminLayout} title="404 Error" description="Hand-built SPA error page." />} />
            <Route path="/errors/405" element={<GenericMigratedPage Layout={AdminLayout} title="405 Error" description="Hand-built SPA error page." />} />
            <Route path="/errors/500" element={<GenericMigratedPage Layout={AdminLayout} title="500 Error" description="Hand-built SPA error page." />} />
            <Route path="/frontend" element={<FrontendHubPage Layout={AdminLayout} />} />
            <Route path="/frontend/about" element={<EndpointDataPage Layout={AdminLayout} title="Frontend About" endpoint="/about" />} />
            <Route path="/frontend/news" element={<EndpointDataPage Layout={AdminLayout} title="Frontend News" endpoint="/news" />} />
            <Route path="/frontend/events" element={<EndpointDataPage Layout={AdminLayout} title="Frontend Events" endpoint="/events" />} />
            <Route path="/frontend/notices" element={<EndpointDataPage Layout={AdminLayout} title="Frontend Notices" endpoint="/notices" />} />
            <Route path="/frontend/contact" element={<EndpointDataPage Layout={AdminLayout} title="Frontend Contact" endpoint="/contact" />} />
            <Route path="/frontend/result" element={<EndpointDataPage Layout={AdminLayout} title="Frontend Result" endpoint="/result" />} />
            <Route path="/frontend/page" element={<EndpointDataPage Layout={AdminLayout} title="Frontend Dynamic Page" endpoint="/page/about-us" />} />
            <Route path="/frontend-landing" element={<FrontendHubPage Layout={AdminLayout} />} />
            <Route path="/frontend-landing/school" element={<GenericMigratedPage Layout={AdminLayout} title="Frontend Landing School" description="Hand-built SPA page replacing frontend-landing/school_landing.blade.php." />} />
            <Route path="/layouts/app" element={<GenericMigratedPage Layout={AdminLayout} title="Layouts App" description="Hand-built SPA page replacing layouts/app.blade.php." />} />
            <Route path="/parent-panel/pages" element={<PanelHubPage Layout={AdminLayout} />} />
            <Route path="/parent-panel/dashboard" element={<EndpointDataPage Layout={AdminLayout} title="Parent Panel Dashboard" endpoint="/parent-panel-dashboard" />} />
            <Route path="/parent-panel/profile" element={<PanelProfileViewPage Layout={AdminLayout} title="Parent Profile" endpoint="/parent-panel/profile" />} />
            <Route path="/parent-panel/profile/edit" element={<PanelProfileEditPage Layout={AdminLayout} title="Edit Parent Profile" loadEndpoint="/parent-panel/profile/edit" saveEndpoint="/parent-panel/profile/update" />} />
            <Route path="/parent-panel/password/update" element={<PanelPasswordPage Layout={AdminLayout} title="Update Parent Password" saveEndpoint="/parent-panel/password/update/store" />} />
            <Route path="/parent-panel/notices" element={<PanelListPage Layout={AdminLayout} title="Parent Notices" endpoint="/parent-panel-notices" preferredKey="notice-boards" />} />
            <Route path="/parent-panel/attendance" element={<EndpointDataPage Layout={AdminLayout} title="Parent Panel Attendance" endpoint="/parent-panel-attendance" />} />
            <Route path="/parent-panel/class-routine" element={<EndpointDataPage Layout={AdminLayout} title="Parent Panel Class Routine" endpoint="/parent-panel-class-routine" />} />
            <Route path="/parent-panel/exam-routine" element={<EndpointDataPage Layout={AdminLayout} title="Parent Panel Exam Routine" endpoint="/parent-panel-exam-routine" />} />
            <Route path="/parent-panel/subject-list" element={<EndpointDataPage Layout={AdminLayout} title="Parent Panel Subject List" endpoint="/parent-panel-subject-list" />} />
            <Route path="/parent-panel/homework-list" element={<EndpointDataPage Layout={AdminLayout} title="Parent Panel Homework List" endpoint="/parent-panel-homeworks" />} />
            <Route path="/parent-panel/fees" element={<EndpointDataPage Layout={AdminLayout} title="Parent Panel Fees" endpoint="/parent-panel-fees" />} />
            <Route path="/parent-panel/marksheet" element={<EndpointDataPage Layout={AdminLayout} title="Parent Panel Marksheet" endpoint="/parent-panel-marksheet" />} />
            <Route path="/parent-panel/books" element={<PanelListPage Layout={AdminLayout} title="Parent Books" endpoint="/parent/panel/books" preferredKey="book" />} />
            <Route path="/parent-panel/issue-books" element={<PanelListPage Layout={AdminLayout} title="Parent Issue Books" endpoint="/parent/panel/issue-books" preferredKey="issue_book" />} />
            <Route path="/student-panel/pages" element={<PanelHubPage Layout={AdminLayout} />} />
            <Route path="/student-panel/dashboard" element={<EndpointDataPage Layout={AdminLayout} title="Student Panel Dashboard" endpoint="/student-panel-dashboard" />} />
            <Route path="/student-panel/profile" element={<PanelProfileViewPage Layout={AdminLayout} title="Student Profile" endpoint="/student-panel/profile" />} />
            <Route path="/student-panel/profile/edit" element={<PanelProfileEditPage Layout={AdminLayout} title="Edit Student Profile" loadEndpoint="/student-panel/profile/edit" saveEndpoint="/student-panel/profile/update" />} />
            <Route path="/student-panel/password/update" element={<PanelPasswordPage Layout={AdminLayout} title="Update Student Password" saveEndpoint="/student-panel/password/update/store" />} />
            <Route path="/student-panel/notices" element={<PanelListPage Layout={AdminLayout} title="Student Notices" endpoint="/student-panel-notices" preferredKey="notice-boards" />} />
            <Route path="/student-panel/gmeet" element={<PanelListPage Layout={AdminLayout} title="Student Gmeet" endpoint="/student-panel-gmeet" preferredKey="gmeets" />} />
            <Route path="/student-panel/attendance" element={<EndpointDataPage Layout={AdminLayout} title="Student Panel Attendance" endpoint="/student-panel-attendance" />} />
            <Route path="/student-panel/class-routine" element={<EndpointDataPage Layout={AdminLayout} title="Student Panel Class Routine" endpoint="/student-panel-class-routine" />} />
            <Route path="/student-panel/exam-routine" element={<EndpointDataPage Layout={AdminLayout} title="Student Panel Exam Routine" endpoint="/student-panel-exam-routine" />} />
            <Route path="/student-panel/subject-list" element={<EndpointDataPage Layout={AdminLayout} title="Student Panel Subject List" endpoint="/student-panel-subject-list" />} />
            <Route path="/student-panel/homeworks" element={<EndpointDataPage Layout={AdminLayout} title="Student Panel Homeworks" endpoint="/stundet/panel/homeworks" />} />
            <Route path="/student-panel/fees" element={<EndpointDataPage Layout={AdminLayout} title="Student Panel Fees" endpoint="/student-panel-fees" />} />
            <Route path="/student-panel/marksheet" element={<EndpointDataPage Layout={AdminLayout} title="Student Panel Marksheet" endpoint="/student-panel-marksheet" />} />
            <Route path="/student-panel/books" element={<PanelListPage Layout={AdminLayout} title="Student Books" endpoint="/stundet/panel/books" preferredKey="book" />} />
            <Route path="/student-panel/issue-books" element={<PanelListPage Layout={AdminLayout} title="Student Issue Books" endpoint="/stundet/panel/issue-books" preferredKey="issue_book" />} />
            <Route path="/student-panel/online-exam" element={<PanelListPage Layout={AdminLayout} title="Student Online Exams" endpoint="/student-panel-online-examination" detailBase="/student-panel/online-exam" />} />
            <Route path="/student-panel/online-exam/:id/view" element={<OnlineExamViewPage Layout={AdminLayout} />} />
            <Route path="/student-panel/online-exam/:id/result" element={<OnlineExamResultPage Layout={AdminLayout} />} />
            <Route path="/home" element={<GenericMigratedPage Layout={AdminLayout} title="Home" description="Hand-built SPA page replacing home.blade.php." />} />
            <Route path="/index" element={<GenericMigratedPage Layout={AdminLayout} title="Index" description="Hand-built SPA page replacing index.blade.php." />} />
            <Route path="/welcome" element={<GenericMigratedPage Layout={AdminLayout} title="Welcome" description="Hand-built SPA page replacing welcome.blade.php." />} />
            <Route path="*" element={<SpaNotFound />} />
        </Routes>
    );
}

