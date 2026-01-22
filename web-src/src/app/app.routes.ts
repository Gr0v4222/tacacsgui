import { Routes } from '@angular/router';
import { Login } from './pages/auth/login/login';
import { Dashboard } from './pages/dashboard/dashboard';
import { UserList } from './pages/users/user-list/user-list';
import { DeviceList } from './pages/devices/device-list/device-list';

export const routes: Routes = [
  { path: '', redirectTo: '/dashboard', pathMatch: 'full' },
  { path: 'login', component: Login },
  { path: 'dashboard', component: Dashboard },
  { path: 'users', component: UserList },
  { path: 'devices', component: DeviceList },
  { path: '**', redirectTo: '/dashboard' }
];
