import { GdtEntry } from './GdtEntry'
import { ObjectType, Field } from 'type-graphql'

@ObjectType()
export class GdtSumPerEmail {
  constructor(email: string, summe: number) {
    this.email = email
    this.summe = summe
  }

  @Field(() => String)
  email: string

  @Field(() => Number)
  summe: number
}

@ObjectType()
export class GdtEntryList {
  constructor(json: any) {
    this.state = json.state
    this.count = json.count
    this.gdtEntries = json.gdtEntries ? json.gdtEntries.map((json: any) => new GdtEntry(json)) : []
    this.gdtSum = json.gdtSum
    this.timeUsed = json.timeUsed
  }

  @Field(() => String)
  state: string

  @Field(() => Number)
  count: number

  @Field(() => [GdtEntry])
  gdtEntries: GdtEntry[]

  @Field(() => Number)
  gdtSum: number

  @Field(() => Number)
  timeUsed: number
}