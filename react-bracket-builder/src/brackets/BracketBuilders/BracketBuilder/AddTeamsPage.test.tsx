import { render } from '@testing-library/react'
import { AddTeamsPage } from './AddTeamsPage'
import { fourTeamMatchTree } from '../../shared/models/MatchTreeFakeFactory'

describe('AddTeamsPage', () => {
  test('renders page correctly', () => {
    const { asFragment } = render(
      <AddTeamsPage
        handleBack={() => {}}
        handleSaveBracket={() => {}}
        matchTree={fourTeamMatchTree()}
      />
    )
    expect(asFragment()).toMatchSnapshot()
  })
})
