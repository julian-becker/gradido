import { BaseEntity, Entity, PrimaryGeneratedColumn, Column, JoinColumn, OneToOne } from 'typeorm'
import { LoginUser } from '../LoginUser'

@Entity('login_user_backups')
export class LoginUserBackup extends BaseEntity {
  @PrimaryGeneratedColumn('increment', { unsigned: true })
  id: number

  @Column({ type: 'text', name: 'passphrase', nullable: false })
  passphrase: string

  @Column({ name: 'mnemonic_type', default: -1 })
  mnemonicType: number

  @OneToOne(() => LoginUser, { nullable: false })
  @JoinColumn({ name: 'user_id' })
  loginUser: LoginUser
}
