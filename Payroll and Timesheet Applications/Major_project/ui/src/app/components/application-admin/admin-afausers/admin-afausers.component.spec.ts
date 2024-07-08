import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminAfausersComponent } from './admin-afausers.component';

describe('AdminAfausersComponent', () => {
  let component: AdminAfausersComponent;
  let fixture: ComponentFixture<AdminAfausersComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminAfausersComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(AdminAfausersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
