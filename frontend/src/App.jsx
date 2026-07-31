import { BrowserRouter, Navigate, Route, Routes } from "react-router-dom";
import { AuthProvider, useAuth } from "./AuthContext";
import Layout from "./components/Layout";
import Login from "./pages/Login";
import HomeDashboard from "./pages/HomeDashboard";
import ModulePage from "./pages/ModulePage";
import Followups from "./pages/Followups";
import EntityDetail from "./pages/EntityDetail";
import Sales from "./pages/Sales";
import SaleForm from "./pages/SaleForm";
import Loyalty from "./pages/Loyalty";
import Reports from "./pages/Reports";
import Settings from "./pages/Settings";
import Staff from "./pages/Staff";
import StaffPerformance from "./pages/StaffPerformance";
import EventCapture from "./pages/EventCapture";
import Exhibitions from "./pages/Exhibitions";
import ExhibitionDetail from "./pages/ExhibitionDetail";
import Marketing from "./pages/Marketing";
import RetentionDashboard from "./pages/RetentionDashboard";
import CustomerRetentionProfile from "./pages/CustomerRetentionProfile";
import MySmartWork from "./pages/MySmartWork";
import SmartTaskList from "./pages/SmartTaskList";
import TaskRules from "./pages/TaskRules";
import MessageTemplates from "./pages/MessageTemplates";
import FestivalCampaigns from "./pages/FestivalCampaigns";
import PrivilegeCards from "./pages/PrivilegeCards";
import Rewards from "./pages/Rewards";
import Notifications from "./pages/Notifications";
import CustomerCategories from "./pages/CustomerCategories";
import UnifiedInbox from "./pages/UnifiedInbox";
import AdCampaigns from "./pages/AdCampaigns";
import VideoSales from "./pages/VideoSales";
import VideoRoom from "./pages/VideoRoom";
function Guard() {
    const { user } = useAuth();
    return user ? <Layout /> : <Navigate to="/login" replace />;
}
export default function App() {
    return (
        <BrowserRouter>
            <AuthProvider>
                <Routes>
                    <Route path="/login" element={<Login />} />
                        <Route path="/capture/:token" element={<EventCapture />} />
                        <Route path="/video-invite/:token" element={<VideoRoom guest />} />
                    <Route element={<Guard />}>
                        <Route index element={<HomeDashboard />} />
                        <Route
                            path="leads"
                            element={<ModulePage module="leads" />}
                        />
                        <Route
                            path="leads/add"
                            element={<ModulePage module="leads" />}
                        />
                        <Route
                            path="leads/:id"
                            element={<EntityDetail type="leads" />}
                        />
                        <Route path="followups" element={<Followups />} />
                        <Route
                            path="customers"
                            element={<ModulePage module="customers" />}
                        />
                        <Route
                            path="customers/:id"
                            element={<EntityDetail type="customers" />}
                        />
                        <Route
                            path="customers/:id/retention"
                            element={<CustomerRetentionProfile />}
                        />
                        <Route path="sales" element={<Sales />} />
                        <Route path="sales/create" element={<SaleForm />} />
                        <Route
                            path="custom-orders"
                            element={<ModulePage module="custom-orders" />}
                        />
                        <Route
                            path="custom-orders/:id"
                            element={<EntityDetail type="custom-orders" />}
                        />
                        <Route path="exhibitions" element={<Exhibitions />} />
                        <Route
                            path="exhibitions/:id"
                            element={<ExhibitionDetail />}
                        />
                        <Route path="marketing" element={<Marketing />} />
                        <Route path="inbox" element={<UnifiedInbox />} />
                        <Route path="ad-campaigns" element={<AdCampaigns />} />
                        <Route path="video-sales" element={<VideoSales />} />
                        <Route path="video-sales/:id/room" element={<VideoRoom />} />
                        <Route
                            path="retention"
                            element={<RetentionDashboard />}
                        />
                        <Route
                            path="retention/messages"
                            element={<RetentionDashboard />}
                        />
                        <Route
                            path="retention/winback"
                            element={<RetentionDashboard fixedType="winback" />}
                        />
                        <Route path="smart-work" element={<MySmartWork />} />
                        <Route path="smart-tasks" element={<SmartTaskList />} />
                        <Route
                            path="smart-tasks/today"
                            element={<SmartTaskList preset="today" />}
                        />
                        <Route
                            path="smart-tasks/overdue"
                            element={<SmartTaskList preset="overdue" />}
                        />
                        <Route
                            path="smart-tasks/my"
                            element={<SmartTaskList preset="my-tasks" />}
                        />
                        <Route path="task-rules" element={<TaskRules />} />
                        <Route
                            path="message-templates"
                            element={<MessageTemplates />}
                        />
                        <Route
                            path="festival-campaigns"
                            element={<FestivalCampaigns />}
                        />
                        <Route
                            path="offers"
                            element={<ModulePage module="offers" />}
                        />
                        <Route path="loyalty" element={<Loyalty />} />
                        <Route
                            path="gift-cards"
                            element={<ModulePage module="gift-cards" />}
                        />
                        <Route
                            path="privilege-cards"
                            element={<PrivilegeCards />}
                        />
                        <Route
                            path="tasks"
                            element={<ModulePage module="tasks" />}
                        />
                        <Route path="reports" element={<Reports />} />
                        <Route path="rewards" element={<Rewards />} />
                        <Route
                            path="notifications"
                            element={<Notifications />}
                        />
                        <Route
                            path="category-rules"
                            element={<CustomerCategories />}
                        />
                        <Route path="settings" element={<Settings />} />
                        <Route path="staff" element={<Staff />} />
                        <Route
                            path="staff/:id"
                            element={<StaffPerformance />}
                        />
                    </Route>
                    <Route path="*" element={<Navigate to="/" />} />
                </Routes>
            </AuthProvider>
        </BrowserRouter>
    );
}
