import Button from '../../ui/Button'
import { bracketApi } from '../../brackets/shared'
import { H4 } from '../../elements/typography/H4'
import { GoLivePageContainer } from './GoLivePageContainer'
import { useState } from 'react'
import { GoLiveSubPageProps } from './types'
import { logger } from '../../utils/Logger'
import Select from 'react-select'

interface StatusPageProps extends GoLiveSubPageProps {}

interface Option {
  value: 'publish' | 'upcoming'
  label: string
}

export const StatusPage = (props: StatusPageProps) => {
  const [loading, setLoading] = useState(false)

  const options: Option[] = [
    { value: 'publish', label: 'Live' },
    { value: 'upcoming', label: 'Upcoming' },
  ]

  const defaultOption =
    options.find((o) => o.value === (props.bracket.status as Option['value'])) ||
    options[0]

  const [selectedOption, setSelectedOption] = useState<Option>(defaultOption)

  const onContinue = async () => {
    setLoading(true)
    try {
      const updated = await bracketApi.updateBracket(props.bracket.id, {
        status: selectedOption.value,
      })
      props.setBracket?.(updated)
      props.navigate('next')
    } catch (error) {
      logger.error('Failed to update bracket status', error)
    } finally {
      setLoading(false)
    }
  }

  const onChange = (option: Option | null) => {
    if (option) setSelectedOption(option)
  }

  return (
    <GoLivePageContainer title="Go Live!" subTitle={props.bracket.title} {...props}>
      <H4 className="tw-text-center tw-mb-16 sm:tw-mb-30 !tw-text-white/50">
        Select Bracket Status
      </H4>
      <div className="tw-flex tw-flex-col tw-justify-center tw-items-center tw-pb-60">
        <div className="tw-w-full tw-max-w-[250px] sm:tw-max-w-[350px]">
          <Select
            options={options}
            defaultValue={defaultOption}
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


