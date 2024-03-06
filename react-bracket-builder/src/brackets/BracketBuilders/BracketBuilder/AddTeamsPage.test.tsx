import { jest } from '@jest/globals'
import { render } from '@testing-library/react'
import { AddTeamsPage } from './AddTeamsPage'
import { fourTeamMatchTree } from '../../shared/models/MatchTreeFakeFactory'

describe('AddTeamsPage', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })
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
