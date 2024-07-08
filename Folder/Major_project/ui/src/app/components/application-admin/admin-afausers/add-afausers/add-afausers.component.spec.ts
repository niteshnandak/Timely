import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AddAfausersComponent } from './add-afausers.component';

describe('AddAfausersComponent', () => {
  let component: AddAfausersComponent;
  let fixture: ComponentFixture<AddAfausersComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AddAfausersComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(AddAfausersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
