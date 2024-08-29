import Button from '../../ui/Button'
import { bracketApi } from '../../brackets/shared'
import { H4 } from '../../elements/typography/H4'
import { GoLivePageContainer } from './GoLivePageContainer'
import { useState } from 'react'
import { GoLiveSubPageProps } from './types'
import { logger } from '../../utils/Logger'
import Select from 'react-select'

interface BracketTypePageProps extends GoLiveSubPageProps {}
interface Option {
  value: string
  label: string
}

export const BracketTypePage = (props: BracketTypePageProps) => {
  const [loading, setLoading] = useState(false)

  const options: Option[] = [
    { value: 'standard', label: 'Standard' },
    { value: 'voting', label: 'Voting' },
  ]
  const [selectedOption, setSelectedOption] = useState(options[0])

  const onContinue = async () => {
    setLoading(true)
    try {
      await bracketApi.updateBracket(props.bracket.id, {
        isVoting: selectedOption.value === 'voting',
      })
      props.navigate('next')
    } catch (error) {
      logger.error('Failed to update bracket', error)
    } finally {
      setLoading(false)
    }
  }

  const onChange = (option: Option) => {
    setSelectedOption(option)
  }

  return (
    <GoLivePageContainer
      title="Go Live!"
      subTitle={props.bracket.title}
      {...props}
    >
      <H4 className="tw-text-center tw-mb-16 sm:tw-mb-30">
        Select Tournament Type
      </H4>
      <div className="tw-flex tw-flex-col tw-justify-center tw-items-center tw-pb-60">
        <div className="tw-w-full tw-max-w-[250px] sm:tw-max-w-[350px]">
          <Select
            options={options}
            defaultValue={options[0]}
            classNamePrefix="react-select"
            isSearchable={false}
            onChange={onChange}
          />
        </div>
      </div>
      <Button
        disabled={loading}
        color="green"
        onClick={onContinue}
        className="tw-mt-15"
        variant="outlined"
      >
        Next
      </Button>
    </GoLivePageContainer>
  )
}
