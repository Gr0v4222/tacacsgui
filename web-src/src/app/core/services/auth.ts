import { Injectable } from '@angular/core';
import { Api } from './api';
import { Observable, BehaviorSubject } from 'rxjs';
import { tap } from 'rxjs/operators';

@Injectable({
  providedIn: 'root',
})
export class Auth {
  private isAuthenticatedSubject = new BehaviorSubject<boolean>(false);
  public isAuthenticated$ = this.isAuthenticatedSubject.asObservable();

  constructor(private api: Api) {
    this.checkAuthStatus();
  }

  private checkAuthStatus(): void {
    // Check if user is authenticated (e.g., from session or local storage)
    const isAuth = localStorage.getItem('isAuthenticated') === 'true';
    this.isAuthenticatedSubject.next(isAuth);
  }

  signIn(username: string, password: string): Observable<any> {
    return this.api.post('/auth/signin/', { username, password }).pipe(
      tap((response: any) => {
        if (response.success) {
          localStorage.setItem('isAuthenticated', 'true');
          this.isAuthenticatedSubject.next(true);
        }
      })
    );
  }

  signOut(): Observable<any> {
    return this.api.get('/auth/signout/').pipe(
      tap(() => {
        localStorage.removeItem('isAuthenticated');
        this.isAuthenticatedSubject.next(false);
      })
    );
  }

  isAuthenticated(): boolean {
    return this.isAuthenticatedSubject.value;
  }
}
