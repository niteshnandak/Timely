import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AuthmanageComponent } from './authmanage.component';

describe('AuthmanageComponent', () => {
  let component: AuthmanageComponent;
  let fixture: ComponentFixture<AuthmanageComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AuthmanageComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(AuthmanageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
