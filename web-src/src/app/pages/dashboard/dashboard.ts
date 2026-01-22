import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { Auth } from '../../core/services/auth';

@Component({
  selector: 'app-dashboard',
  imports: [CommonModule, RouterModule],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.scss',
})
export class Dashboard implements OnInit {
  stats = {
    users: 0,
    devices: 0,
    groups: 0,
    activeSessions: 0
  };

  constructor(private auth: Auth, private router: Router) {}

  ngOnInit(): void {
    // Load dashboard stats from API
    this.loadStats();
  }

  loadStats(): void {
    // This will be implemented with actual API calls
    this.stats = {
      users: 45,
      devices: 23,
      groups: 8,
      activeSessions: 12
    };
  }

  logout(): void {
    this.auth.signOut().subscribe({
      next: () => {
        this.router.navigate(['/login']);
      },
      error: (error) => {
        console.error('Logout error:', error);
        this.router.navigate(['/login']);
      }
    });
  }
}
