/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable @typescript-eslint/explicit-module-boundary-types */
import { Entity, BaseEntity, Column } from 'typeorm'
import { ObjectType, Field } from 'type-graphql'

@Entity()
@ObjectType()
export class User extends BaseEntity {
  /*
  @Field(() => ID)
  @PrimaryGeneratedColumn()
  id: number
  */
  constructor(json: any) {
    super()
    this.email = json.email
    this.firstName = json.first_name
    this.lastName = json.last_name
    this.username = json.username
    this.description = json.description
    this.language = json.language
  }

  @Field(() => String)
  @Column({ length: 191 })
  email: string

  @Field(() => String)
  @Column({ length: 150 })
  firstName: string

  @Field(() => String)
  @Column()
  lastName: string

  @Field(() => String)
  @Column()
  username: string

  @Field(() => String)
  @Column('text')
  description: string

  /*
  @Field(() => String)
  @Column({ length: 64 })
  pubkey: string

  // not sure about the type here. Maybe better to have a string
  @Field(() => number)
  @Column({ type: 'datetime' })
  created: number

  @Field(() => Boolean)
  @Column({ default: false })
  emailChecked: boolean

  @Field(() => Boolean)
  @Column({ default: false })
  passphraseShown: boolean
  */

  @Field(() => String)
  @Column({ default: 'de' })
  language: string

  /*
  @Field(() => Boolean)
  @Column({ default: false })
  disabled: boolean
  */

  /* I suggest to have a group as type here
  @Field(() => ID)
  @Column()
  groupId: number

  // what is puvlisherId?
  @Field(() => ID)
  @Column({ default: 0 })
  publisherId: number
  */
}
