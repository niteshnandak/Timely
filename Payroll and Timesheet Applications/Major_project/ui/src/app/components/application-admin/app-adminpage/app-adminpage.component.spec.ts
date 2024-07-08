import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AppAdminpageComponent } from './app-adminpage.component';

describe('AppAdminpageComponent', () => {
  let component: AppAdminpageComponent;
  let fixture: ComponentFixture<AppAdminpageComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AppAdminpageComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(AppAdminpageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
