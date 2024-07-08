import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EditAfausersComponent } from './edit-afausers.component';

describe('EditAfausersComponent', () => {
  let component: EditAfausersComponent;
  let fixture: ComponentFixture<EditAfausersComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [EditAfausersComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(EditAfausersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
